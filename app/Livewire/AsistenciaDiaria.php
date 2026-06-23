<?php

namespace App\Livewire;

use App\Models\Asistencia;
use App\Models\Punto;
use App\Models\Retardo;
use App\Models\Subpunto;
use App\Models\TiemposExtra;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Livewire\Component;
use Livewire\WithFileUploads;

class AsistenciaDiaria extends Component
{
    use WithFileUploads;

    private const ROLES_ASISTENCIA = [
        'GUARDIA',
        'SUPERVISOR',
        'COORDINADOR',
        'PROTECCION',
        'ADMINISTRACION',
        'AGENTE DE SEGURIDAD',
        'CORTADOR',
        'RECEPCION',
        'OFICINA',
        'RANCHO',
        'MONITORISTA',
    ];

    public string $punto = '';

    public string $fecha = '';

    public array $usuarios = [];

    public ?int $registroId = null;

    public bool $modoEdicion = false;

    public array $estatusPorUsuario = [];

    public array $turnosPorUsuario = [];

    public array $fotosNuevas = [];

    public array $fotosExistentes = [];

    public array $eliminarFotos = [];

    public array $minutosRetardo = [];

    public array $tiempoExtraHoras = [];

    public array $tiempoExtraObs = [];

    public string $observaciones = '';

    public array $coberturas = [];

    public string $busquedaCobertura = '';

    public array $resultadosCobertura = [];

    public array $subpuntosMap = [];

    public array $puntosConAsistencia = [];

    public function mount(): void
    {
        $this->fecha = now()->toDateString();
        $this->subpuntosMap = $this->obtenerSubpuntos();
        $this->actualizarPuntosConAsistencia();
    }

    public function updatedPunto(): void
    {
        $this->cargarRegistro();
    }

    public function updatedFecha(): void
    {
        $this->actualizarPuntosConAsistencia();
        $this->cargarRegistro();
    }

    public function updatedBusquedaCobertura(string $value): void
    {
        if (mb_strlen(trim($value)) < 2) {
            $this->resultadosCobertura = [];

            return;
        }

        $seleccionados = collect($this->coberturas)->pluck('id')->all();
        $this->resultadosCobertura = User::query()
            ->where('estatus', 'Activo')
            ->where('name', 'like', '%'.trim($value).'%')
            ->when($seleccionados, fn ($query) => $query->whereNotIn('id', $seleccionados))
            ->orderBy('name')
            ->limit(8)
            ->get(['id', 'name', 'punto'])
            ->map(fn (User $user) => [
                'id' => $user->id,
                'nombre' => $user->name,
                'punto_actual' => $user->punto,
            ])->all();
    }

    public function seleccionarCobertura(int $userId): void
    {
        if (collect($this->coberturas)->contains('id', $userId)) {
            return;
        }

        $user = User::where('estatus', 'Activo')->find($userId);
        if (! $user) {
            return;
        }

        $this->coberturas[] = [
            'id' => $user->id,
            'nombre' => $user->name,
            'subpunto_id' => null,
            'punto' => null,
        ];
        $this->reset('busquedaCobertura', 'resultadosCobertura');
    }

    public function actualizarSubpuntoCobertura(int $indice, $subpuntoId): void
    {
        if (! isset($this->coberturas[$indice])) {
            return;
        }

        $subpunto = Subpunto::with('punto')->find($subpuntoId);
        $this->coberturas[$indice]['subpunto_id'] = $subpunto?->id;
        $this->coberturas[$indice]['punto'] = $subpunto
            ? $subpunto->punto->nombre.' - '.$subpunto->nombre
            : null;
    }

    public function eliminarCobertura(int $indice): void
    {
        if (isset($this->coberturas[$indice])) {
            unset($this->coberturas[$indice]);
            $this->coberturas = array_values($this->coberturas);
        }
    }

    public function guardar(): void
    {
        $idsUsuarios = collect($this->usuarios)->pluck('id')->map(fn ($id) => (int) $id)->all();

        $this->validate([
            'punto' => ['required', 'string', 'max:255'],
            'fecha' => ['required', 'date', 'date_format:Y-m-d'],
            'estatusPorUsuario' => ['required', 'array'],
            'estatusPorUsuario.*' => ['required', 'in:asistio,falto,descanso'],
            'turnosPorUsuario' => ['array'],
            'turnosPorUsuario.*' => ['array'],
            'turnosPorUsuario.*.*' => ['in:dia,tarde,noche'],
            'fotosNuevas.*' => ['nullable', 'image', 'mimes:jpeg,png,jpg,gif', 'max:5120'],
            'minutosRetardo.*' => ['nullable', 'integer', 'min:1', 'max:599'],
            'tiempoExtraHoras.*' => ['nullable', 'numeric', 'min:0.01', 'max:24'],
            'tiempoExtraObs.*' => ['nullable', 'string', 'max:255'],
            'observaciones' => ['nullable', 'string', 'max:255'],
            'coberturas' => ['array'],
            'coberturas.*.id' => ['required', 'integer', 'exists:users,id'],
            'coberturas.*.subpunto_id' => ['required', 'integer', 'exists:subpuntos,id'],
        ], [
            'coberturas.*.subpunto_id.required' => 'Selecciona el punto que cubrirá cada elemento.',
        ]);

        if (! $idsUsuarios) {
            $this->addError('usuarios', 'No hay personal para registrar en este punto.');

            return;
        }

        $idsConEstatus = array_map('intval', array_keys($this->estatusPorUsuario));
        if (array_diff($idsUsuarios, $idsConEstatus) || array_diff($idsConEstatus, $idsUsuarios)) {
            $this->addError('estatusPorUsuario', 'Todos los elementos deben tener un estatus.');

            return;
        }

        $asistentes = $this->idsPorEstatus('asistio');
        $faltas = $this->idsPorEstatus('falto');
        $descansos = $this->idsPorEstatus('descanso');
        $nuevasRutas = [];
        $fotosAEliminar = [];

        DB::beginTransaction();
        try {
            $configuracion = $this->resolverPunto($this->punto);
            $registro = $this->registroId
                ? Asistencia::lockForUpdate()->find($this->registroId)
                : null;

            if (! $registro) {
                $registro = Asistencia::query()
                    ->whereDate('fecha', $this->fecha)
                    ->whereIn('punto', $configuracion['alias'])
                    ->lockForUpdate()
                    ->first();
            }

            $registrador = Auth::user();
            $fotos = $this->fotosExistentes;
            foreach ($this->eliminarFotos as $userId => $eliminar) {
                if ($eliminar && isset($fotos[$userId])) {
                    $fotosAEliminar[] = $fotos[$userId];
                    unset($fotos[$userId]);
                }
            }

            $ruta = 'asistencias/'.Str::slug($registrador->name).'/'.$this->fecha;
            foreach ($this->fotosNuevas as $userId => $foto) {
                if (! $foto) {
                    continue;
                }
                if (isset($fotos[$userId])) {
                    $fotosAEliminar[] = $fotos[$userId];
                }
                $nuevaRuta = $foto->store($ruta, 'public');
                $nuevasRutas[] = $nuevaRuta;
                $fotos[$userId] = $nuevaRuta;
            }

            $datos = [
                'user_id' => $registro?->user_id ?? $registrador->id,
                'fecha' => $this->fecha,
                'hora_asistencia' => $registro?->hora_asistencia ?? now('America/Mexico_City')->toTimeString(),
                'elementos_enlistados' => json_encode($asistentes),
                'turnos' => json_encode($this->soloDatosDeAsistentes($this->turnosPorUsuario, $asistentes)),
                'faltas' => json_encode($faltas),
                'descansos' => json_encode($descansos),
                'coberturas' => json_encode(collect($this->coberturas)->map(fn ($item) => [
                    'id' => (int) $item['id'],
                    'subpunto_id' => (int) $item['subpunto_id'],
                ])->values()->all()),
                'observaciones' => trim($this->observaciones) ?: 'Ninguna',
                'punto' => $configuracion['nombre'],
                'empresa' => $registro?->empresa ?? ($registrador->empresa ?? 'PSC'),
                'fotos_asistentes' => json_encode($fotos),
            ];

            if ($registro) {
                $registro->update($datos);
            } else {
                $registro = Asistencia::create($datos);
            }

            $this->sincronizarRetardos($registro, $asistentes, $registrador->id);
            $this->sincronizarTiemposExtra($registro, $asistentes, $registrador);

            DB::commit();

            foreach (array_unique($fotosAEliminar) as $foto) {
                Storage::disk('public')->delete($foto);
            }

            $this->registroId = $registro->id;
            $this->modoEdicion = true;
            $this->fotosExistentes = $fotos;
            $this->reset('fotosNuevas', 'eliminarFotos');
            $this->actualizarPuntosConAsistencia();
            session()->flash('success', 'La asistencia se guardó correctamente.');
        } catch (\Throwable $e) {
            DB::rollBack();
            foreach ($nuevasRutas as $ruta) {
                Storage::disk('public')->delete($ruta);
            }
            Log::error('Error al guardar asistencia unificada de Operaciones', [
                'error' => $e->getMessage(),
                'punto' => $this->punto,
                'fecha' => $this->fecha,
            ]);
            $this->addError('guardado', 'No fue posible guardar la asistencia. Inténtalo nuevamente.');
        }
    }

    public function render()
    {
        return view('livewire.asistencia-diaria', [
            'resumen' => [
                'asistencias' => count($this->idsPorEstatus('asistio')),
                'faltas' => count($this->idsPorEstatus('falto')),
                'descansos' => count($this->idsPorEstatus('descanso')),
            ],
        ]);
    }

    private function cargarRegistro(): void
    {
        $this->resetDatosRegistro();
        if (! $this->punto || ! $this->fecha) {
            return;
        }

        $configuracion = $this->resolverPunto($this->punto);
        $registro = Asistencia::query()
            ->whereDate('fecha', $this->fecha)
            ->whereIn('punto', $configuracion['alias'])
            ->latest('id')
            ->first();

        $idsGuardados = $registro ? collect([
            ...$this->decodificar($registro->elementos_enlistados),
            ...$this->decodificar($registro->faltas),
            ...$this->decodificar($registro->descansos),
        ])->map(fn ($id) => (int) $id)->unique()->all() : [];

        $usuariosActuales = User::query()
            ->where('estatus', 'Activo')
            ->whereIn(DB::raw('UPPER(TRIM(rol))'), $this->rolesNormalizados())
            ->whereIn('punto', $configuracion['alias'])
            ->pluck('id')->all();

        $ids = array_values(array_unique([...$usuariosActuales, ...$idsGuardados]));
        $this->usuarios = User::withTrashed()
            ->with('solicitudAlta.documentacion')
            ->whereIn('id', $ids)
            ->orderBy('name')
            ->get()
            ->map(fn (User $user) => [
                'id' => $user->id,
                'name' => $user->name,
                'empresa' => $user->empresa,
                'punto' => $user->punto,
                'rol' => $user->rol,
                'activo' => $user->estatus === 'Activo' && ! $user->trashed(),
                'foto' => $user->solicitudAlta?->documentacion?->arch_foto,
            ])->all();

        foreach ($ids as $id) {
            $this->estatusPorUsuario[$id] = 'descanso';
            // Livewire necesita un arreglo por usuario para tratar cada turno como
            // una opción independiente y no como un único booleano compartido.
            $this->turnosPorUsuario[$id] = [];
        }

        if (! $registro) {
            return;
        }

        $this->registroId = $registro->id;
        $this->modoEdicion = true;
        foreach ($this->decodificar($registro->elementos_enlistados) as $id) {
            $this->estatusPorUsuario[$id] = 'asistio';
        }
        foreach ($this->decodificar($registro->faltas) as $id) {
            $this->estatusPorUsuario[$id] = 'falto';
        }
        foreach ($this->decodificar($registro->descansos) as $id) {
            $this->estatusPorUsuario[$id] = 'descanso';
        }

        $this->turnosPorUsuario = array_replace(
            $this->turnosPorUsuario,
            $this->decodificar($registro->turnos)
        );
        $this->fotosExistentes = $this->decodificar($registro->fotos_asistentes);
        $this->observaciones = $registro->observaciones === 'Ninguna' ? '' : (string) $registro->observaciones;
        $this->cargarCoberturas($registro);

        Retardo::where('asistencia_id', $registro->id)->get()->each(function (Retardo $retardo) {
            $this->minutosRetardo[$retardo->user_id] = $retardo->minutos_retardo;
        });

        TiemposExtra::query()
            ->where(function ($query) use ($registro, $idsGuardados) {
                $query->where('asistencia_id', $registro->id)
                    ->orWhere(function ($legacy) use ($registro, $idsGuardados) {
                        $legacy->whereNull('asistencia_id')
                            ->whereDate('fecha', $registro->fecha)
                            ->whereIn('user_id', $idsGuardados)
                            ->where('autorizado_por', $registro->usuario?->name);
                    });
            })->get()->each(function (TiemposExtra $extra) {
                $this->tiempoExtraHoras[$extra->user_id] = $this->horaDecimal($extra->total_horas);
                $this->tiempoExtraObs[$extra->user_id] = $extra->observaciones;
            });
    }

    private function resetDatosRegistro(): void
    {
        $this->reset([
            'usuarios', 'registroId', 'modoEdicion', 'estatusPorUsuario', 'turnosPorUsuario',
            'fotosNuevas', 'fotosExistentes', 'eliminarFotos', 'minutosRetardo',
            'tiempoExtraHoras', 'tiempoExtraObs', 'observaciones', 'coberturas',
            'busquedaCobertura', 'resultadosCobertura',
        ]);
        $this->resetValidation();
    }

    private function sincronizarRetardos(Asistencia $registro, array $asistentes, int $registradorId): void
    {
        $valores = $this->soloDatosDeAsistentes($this->minutosRetardo, $asistentes);
        Retardo::where('asistencia_id', $registro->id)
            ->whereNotIn('user_id', array_map('intval', array_keys($valores)) ?: [0])
            ->delete();

        foreach ($valores as $userId => $minutos) {
            if (! $minutos) {
                continue;
            }
            Retardo::updateOrCreate(
                ['asistencia_id' => $registro->id, 'user_id' => $userId],
                ['fecha' => $this->fecha, 'minutos_retardo' => $minutos, 'registrado_por' => $registradorId]
            );
        }
    }

    private function sincronizarTiemposExtra(Asistencia $registro, array $asistentes, User $registrador): void
    {
        $horas = $this->soloDatosDeAsistentes($this->tiempoExtraHoras, $asistentes);
        $idsConHoras = collect($horas)->filter()->keys()->map(fn ($id) => (int) $id)->all();

        TiemposExtra::where('asistencia_id', $registro->id)
            ->whereNotIn('user_id', $idsConHoras ?: [0])
            ->delete();

        foreach ($horas as $userId => $cantidad) {
            if (! $cantidad) {
                continue;
            }

            $extra = TiemposExtra::query()
                ->where('user_id', $userId)
                ->whereDate('fecha', $this->fecha)
                ->where(function ($query) use ($registro, $registrador) {
                    $query->where('asistencia_id', $registro->id)
                        ->orWhere(function ($legacy) use ($registrador) {
                            $legacy->whereNull('asistencia_id')
                                ->where('autorizado_por', $registrador->name);
                        });
                })->first() ?? new TiemposExtra;

            $extra->fill([
                'asistencia_id' => $registro->id,
                'user_id' => $userId,
                'fecha' => $this->fecha,
                'total_horas' => $this->decimalAHora((float) $cantidad),
                'observaciones' => $this->tiempoExtraObs[$userId] ?? 'Ninguna',
                'autorizado_por' => $registrador->name,
            ])->save();
        }
    }

    private function cargarCoberturas(Asistencia $registro): void
    {
        foreach ($this->decodificar($registro->coberturas) as $cobertura) {
            $user = User::withTrashed()->find($cobertura['id'] ?? null);
            $subpunto = Subpunto::with('punto')->find($cobertura['subpunto_id'] ?? null);
            if ($user) {
                $this->coberturas[] = [
                    'id' => $user->id,
                    'nombre' => $user->name,
                    'subpunto_id' => $subpunto?->id,
                    'punto' => $subpunto ? $subpunto->punto->nombre.' - '.$subpunto->nombre : null,
                ];
            }
        }
    }

    private function idsPorEstatus(string $estatus): array
    {
        return collect($this->estatusPorUsuario)
            ->filter(fn ($valor) => $valor === $estatus)
            ->keys()->map(fn ($id) => (int) $id)->values()->all();
    }

    private function rolesNormalizados(): array
    {
        return array_values(array_unique(array_map(
            fn (string $rol) => strtoupper(trim($rol)),
            self::ROLES_ASISTENCIA
        )));
    }

    private function soloDatosDeAsistentes(array $datos, array $asistentes): array
    {
        return collect($datos)->filter(
            fn ($valor, $userId) => in_array((int) $userId, $asistentes, true)
        )->all();
    }

    private function resolverPunto(string $valor): array
    {
        $valor = trim(urldecode($valor));
        if (mb_strtoupper($valor) === 'MONTERREY') {
            return [
                'nombre' => 'MONTERREY',
                'alias' => ['KANSAS', 'MONTERREY', 'MTY'],
                'roles' => ['SUPERVISOR', 'APOYO SUPERVISOR', 'K9', 'CORTADOR', 'GUARDIA', 'RECEPCIONISTA'],
            ];
        }

        $subpunto = Subpunto::query()
            ->where('nombre', $valor)
            ->when(is_numeric($valor), fn ($query) => $query->orWhere('codigo', (int) $valor))
            ->first();

        if (! $subpunto) {
            return [
                'nombre' => $valor,
                'alias' => array_values(array_unique([$valor, is_numeric($valor) ? str_pad((string) (int) $valor, 3, '0', STR_PAD_LEFT) : $valor])),
                'roles' => ['GUARDIA'],
            ];
        }

        return [
            'nombre' => $subpunto->nombre,
            'alias' => array_values(array_unique(array_filter([
                $subpunto->nombre,
                $subpunto->codigo !== null ? str_pad((string) $subpunto->codigo, 3, '0', STR_PAD_LEFT) : null,
            ]))),
            'roles' => $subpunto->roles ?: ['GUARDIA'],
        ];
    }

    private function obtenerSubpuntos(): array
    {
        return Punto::with('subpuntos')
            ->get()
            ->sortBy(fn (Punto $punto) => mb_strtoupper(trim($punto->nombre)) === 'MONTERREY'
                ? '0'
                : '1-'.mb_strtoupper(trim($punto->nombre)))
            ->mapWithKeys(function (Punto $punto) {
                $subpuntos = $punto->subpuntos
                    ->sortBy(fn (Subpunto $subpunto) => $subpunto->codigo === null
                        ? '1-'.mb_strtoupper(trim($subpunto->nombre))
                        : '0-'.str_pad((string) $subpunto->codigo, 10, '0', STR_PAD_LEFT))
                    ->map(fn (Subpunto $subpunto) => [
                        'id' => $subpunto->id,
                        'nombre' => $subpunto->nombre,
                        'codigo' => $subpunto->codigo,
                        'valor' => $subpunto->codigo !== null
                            ? str_pad((string) $subpunto->codigo, 3, '0', STR_PAD_LEFT)
                            : $subpunto->nombre,
                    ])->values()->all();

                if (mb_strtoupper($punto->nombre) === 'MONTERREY') {
                    array_unshift($subpuntos, ['id' => null, 'nombre' => 'MONTERREY', 'codigo' => null, 'valor' => 'MONTERREY']);
                }

                return [$punto->nombre => $subpuntos];
            })->all();
    }

    private function actualizarPuntosConAsistencia(): void
    {
        $this->puntosConAsistencia = Asistencia::whereDate('fecha', $this->fecha)
            ->pluck('punto')->map(fn ($punto) => trim($punto))->unique()->values()->all();
    }

    private function decodificar($valor): array
    {
        if (is_array($valor)) {
            return $valor;
        }

        return json_decode($valor ?: '[]', true) ?: [];
    }

    private function horaDecimal(?string $hora): float
    {
        if (! $hora) {
            return 0;
        }
        [$horas, $minutos, $segundos] = array_pad(explode(':', $hora), 3, 0);

        return round((int) $horas + ((int) $minutos / 60) + ((int) $segundos / 3600), 2);
    }

    private function decimalAHora(float $cantidad): string
    {
        $segundosTotales = (int) round($cantidad * 3600);
        $horas = intdiv($segundosTotales, 3600);
        $minutos = intdiv($segundosTotales % 3600, 60);
        $segundos = $segundosTotales % 60;

        return sprintf('%02d:%02d:%02d', $horas, $minutos, $segundos);
    }
}
