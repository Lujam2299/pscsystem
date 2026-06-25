<?php

namespace App\Livewire;

use App\Models\Asistencia;
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

class SupervisorAsistenciaDiaria extends Component
{
    use WithFileUploads;

    private const ROLES_ASISTENCIA = [
        'GUARDIA',
        'AGENTE DE SEGURIDAD',
        'PROTECCION',
        'RECEPCION',
        'CORTADOR',
        'K9',
        'APOYO SUPERVISOR',
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

    public array $puntosPermitidos = [];

    public ?string $zonaSupervisor = null;

    public ?string $puntoBaseSupervisor = null;

    public string $mensajeAlcance = '';

    public function mount(): void
    {
        $this->fecha = now()->toDateString();
        $this->puntosPermitidos = $this->resolverPuntosPermitidos();

        if (count($this->puntosPermitidos) === 1) {
            $this->punto = $this->puntosPermitidos[0]['valor'];
            $this->cargarRegistro();
        }
    }

    public function updatedPunto(): void
    {
        $this->cargarRegistro();
    }

    public function updatedFecha(): void
    {
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
        $configuracion = $this->resolverPuntoPermitido($this->punto);
        if (! $configuracion) {
            $this->addError('punto', 'No tienes permiso para registrar asistencia en este punto.');

            return;
        }

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

        $subpuntosPermitidos = collect($this->puntosPermitidos)
            ->pluck('id')
            ->filter()
            ->map(fn ($id) => (int) $id)
            ->all();

        $subpuntosCobertura = collect($this->coberturas)
            ->pluck('subpunto_id')
            ->filter()
            ->map(fn ($id) => (int) $id)
            ->all();

        if (array_diff($subpuntosCobertura, $subpuntosPermitidos)) {
            $this->addError('coberturas', 'Las coberturas deben pertenecer a tus puntos permitidos.');

            return;
        }

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
            $registro = $this->registroId
                ? Asistencia::lockForUpdate()->find($this->registroId)
                : null;

            if ($registro && ((int) $registro->user_id !== (int) Auth::id() || ! in_array($registro->punto, $configuracion['alias'], true))) {
                $this->addError('guardado', 'No tienes permiso para actualizar este registro de asistencia.');
                DB::rollBack();

                return;
            }

            if (! $registro) {
                $registro = Asistencia::query()
                    ->whereDate('fecha', $this->fecha)
                    ->where('user_id', Auth::id())
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
                'user_id' => $registrador->id,
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
                'empresa' => $registrador->empresa ?? 'PSC',
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
            session()->flash('success', 'La asistencia se guardó correctamente.');
        } catch (\Throwable $e) {
            DB::rollBack();
            foreach ($nuevasRutas as $ruta) {
                Storage::disk('public')->delete($ruta);
            }
            Log::error('Error al guardar asistencia de supervisor', [
                'error' => $e->getMessage(),
                'punto' => $this->punto,
                'fecha' => $this->fecha,
                'supervisor_id' => Auth::id(),
            ]);
            $this->addError('guardado', 'No fue posible guardar la asistencia. Inténtalo nuevamente.');
        }
    }

    public function render()
    {
        return view('livewire.supervisor-asistencia-diaria', [
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

        $configuracion = $this->resolverPuntoPermitido($this->punto);
        if (! $configuracion) {
            $this->addError('punto', 'No tienes permiso para registrar asistencia en este punto.');

            return;
        }

        $registro = Asistencia::query()
            ->whereDate('fecha', $this->fecha)
            ->where('user_id', Auth::id())
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
            ->where('empresa', Auth::user()->empresa)
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
            // Cada usuario tiene su propio arreglo de turnos para que Livewire no comparta estados.
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

        TiemposExtra::where('asistencia_id', $registro->id)->get()->each(function (TiemposExtra $extra) {
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

    private function resolverPuntosPermitidos(): array
    {
        $supervisor = Auth::user()->loadMissing('subpuntosSupervisados.punto');
        $base = $supervisor->subpuntosSupervisados->first()
            ?: $this->buscarSubpuntoPorValor($supervisor->punto);

        if (! $base) {
            $puntoActual = trim((string) $supervisor->punto);
            $this->puntoBaseSupervisor = $puntoActual ?: null;

            if ($puntoActual === '') {
                $this->mensajeAlcance = 'No tienes un punto configurado para registrar asistencia.';

                return [];
            }

            $this->mensajeAlcance = 'No se encontró un subpunto configurado para tu usuario. Se usará tu punto actual como único alcance.';

            return [[
                'id' => null,
                'nombre' => $puntoActual,
                'codigo' => null,
                'zona' => null,
                'valor' => $puntoActual,
                'grupo' => 'Punto asignado',
                'alias' => $this->aliasParaValor($puntoActual),
            ]];
        }

        $this->puntoBaseSupervisor = $base->nombre;
        $this->zonaSupervisor = $base->zona;

        if ($base->zona) {
            $subpuntos = Subpunto::with('punto')
                ->where('zona', $base->zona)
                ->orderBy('nombre')
                ->get();
            $this->mensajeAlcance = 'Puedes registrar asistencia de los puntos de la zona '.$base->zona.'.';
        } else {
            $subpuntos = collect([$base]);
            $this->mensajeAlcance = 'Este punto no tiene zona configurada. Solo puedes registrar asistencia de tu punto asignado.';
        }

        return $subpuntos
            ->map(fn (Subpunto $subpunto) => $this->formatearSubpuntoPermitido($subpunto))
            ->values()
            ->all();
    }

    private function resolverPuntoPermitido(string $valor): ?array
    {
        $valor = trim(urldecode($valor));

        return collect($this->puntosPermitidos)->first(function (array $punto) use ($valor) {
            return in_array($valor, $punto['alias'], true) || $valor === $punto['valor'];
        });
    }

    private function formatearSubpuntoPermitido(Subpunto $subpunto): array
    {
        $valor = $subpunto->codigo !== null
            ? str_pad((string) $subpunto->codigo, 3, '0', STR_PAD_LEFT)
            : $subpunto->nombre;

        return [
            'id' => $subpunto->id,
            'nombre' => $subpunto->nombre,
            'codigo' => $subpunto->codigo,
            'zona' => $subpunto->zona,
            'valor' => $valor,
            'grupo' => $subpunto->punto?->nombre ?? 'Puntos',
            'alias' => array_values(array_unique(array_filter([
                $subpunto->nombre,
                $valor,
                $subpunto->codigo !== null ? (string) $subpunto->codigo : null,
            ]))),
        ];
    }

    private function buscarSubpuntoPorValor(?string $valor): ?Subpunto
    {
        if (! $valor) {
            return null;
        }

        $valor = trim($valor);

        return Subpunto::with('punto')
            ->where('nombre', $valor)
            ->when(is_numeric($valor), fn ($query) => $query->orWhere('codigo', (int) $valor))
            ->first();
    }

    private function aliasParaValor(string $valor): array
    {
        return array_values(array_unique(array_filter([
            $valor,
            is_numeric($valor) ? (string) (int) $valor : null,
            is_numeric($valor) ? str_pad((string) (int) $valor, 3, '0', STR_PAD_LEFT) : null,
        ])));
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

            TiemposExtra::updateOrCreate(
                ['asistencia_id' => $registro->id, 'user_id' => $userId],
                [
                    'fecha' => $this->fecha,
                    'total_horas' => $this->decimalAHora((float) $cantidad),
                    'observaciones' => $this->tiempoExtraObs[$userId] ?? 'Ninguna',
                    'autorizado_por' => $registrador->name,
                ]
            );
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
