<?php

namespace App\Livewire;

use App\Models\InspeccionEvidencia;
use App\Models\InspeccionRevisionCaso;
use App\Models\InspeccionUnidad;
use App\Models\Unidades;
use App\Services\AuditLogger;
use App\Services\InspeccionMensajeAgrupador;
use App\Support\Authorization\Permission;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Livewire\Component;
use Throwable;

class InspeccionRevisionDetalle extends Component
{
    public InspeccionRevisionCaso $caso;

    public string $unidadId = '';

    public string $tipo = 'revision';

    public string $resultado = 'con_observaciones';

    public string $kilometraje = '';

    public string $reportadoPor = '';

    public string $observaciones = '';

    public string $notasRevision = '';

    public function mount(InspeccionRevisionCaso $caso): void
    {
        Gate::authorize(Permission::INSPECTION_INBOX_VIEW);
        $this->caso = $caso;
        $this->cargarCaso();
        $this->unidadId = (string) ($caso->unidad_confirmada_id ?: $caso->unidad_sugerida_id ?: '');
        $this->reportadoPor = (string) ($caso->mensajes->pluck('remitente')->filter()->first() ?: '');
        $this->observaciones = $caso->mensajes->pluck('texto')->filter()->implode("\n");
        $this->notasRevision = (string) $caso->notas_revision;
    }

    public function alternarMensaje(int $mensajeId, InspeccionMensajeAgrupador $agrupador): void
    {
        Gate::authorize(Permission::INSPECTION_INBOX_MANAGE);
        abort_if(in_array($this->caso->estado, ['confirmado', 'descartado'], true), 409);

        $mensaje = $this->caso->mensajes()->findOrFail($mensajeId);
        $mensaje->update(['incluido' => ! $mensaje->incluido]);
        $this->caso = $agrupador->analizar($this->caso);
        $this->unidadId = (string) ($this->caso->unidad_sugerida_id ?: '');
        $this->cargarCaso();
    }

    public function guardarRevision(): void
    {
        Gate::authorize(Permission::INSPECTION_INBOX_MANAGE);
        abort_if(in_array($this->caso->estado, ['confirmado', 'descartado'], true), 409);

        $this->validate([
            'unidadId' => ['nullable', 'integer', 'exists:unidades,id'],
            'notasRevision' => ['nullable', 'string', 'max:5000'],
        ]);

        $this->caso->update([
            'unidad_confirmada_id' => $this->unidadId !== '' ? $this->unidadId : null,
            'notas_revision' => $this->notasRevision ?: null,
            'reviewed_by' => auth()->id(),
        ]);

        session()->flash('success', 'Revisión guardada. El caso continúa pendiente de confirmación.');
        $this->cargarCaso();
    }

    public function descartar(): mixed
    {
        Gate::authorize(Permission::INSPECTION_INBOX_MANAGE);
        abort_if($this->caso->estado === 'confirmado', 409);

        $this->caso->update([
            'estado' => 'descartado',
            'notas_revision' => $this->notasRevision ?: null,
            'reviewed_by' => auth()->id(),
        ]);

        app(AuditLogger::class)->record('Bandeja de evidencias', 'Caso descartado', $this->caso);

        return redirect()->route('inspecciones.recepcion.index');
    }

    public function confirmar(): mixed
    {
        Gate::authorize(Permission::INSPECTION_INBOX_MANAGE);
        abort_if(in_array($this->caso->estado, ['confirmado', 'descartado'], true), 409);

        $data = $this->validate([
            'unidadId' => ['required', 'integer', 'exists:unidades,id'],
            'tipo' => ['required', 'in:cambio_turno,entrega,recepcion,revision,mantenimiento'],
            'resultado' => ['required', 'in:sin_novedad,con_observaciones,requiere_revision,requiere_reparacion'],
            'kilometraje' => ['nullable', 'integer', 'min:0', 'max:99999999'],
            'reportadoPor' => ['nullable', 'string', 'max:150'],
            'observaciones' => ['nullable', 'string', 'max:5000'],
            'notasRevision' => ['nullable', 'string', 'max:5000'],
        ]);

        $mensajes = $this->caso->mensajes()->where('incluido', true)->with('archivos')->get();
        $archivos = $mensajes->pluck('archivos')->flatten();
        if ($archivos->isEmpty()) {
            $this->addError('mensajes', 'Incluye al menos un mensaje con imágenes para confirmar.');

            return null;
        }

        if (InspeccionEvidencia::query()->whereIn('sha256', $archivos->pluck('sha256'))->exists()) {
            $this->addError('mensajes', 'Una o más imágenes ya pertenecen a otra inspección.');

            return null;
        }

        $copiados = [];
        $originales = [];

        try {
            $inspeccion = DB::transaction(function () use ($data, $mensajes, $archivos, &$copiados, &$originales): InspeccionUnidad {
                $fecha = $mensajes->min('fecha_mensaje') ?: now();
                $inspeccion = InspeccionUnidad::create([
                    'unidad_id' => $data['unidadId'],
                    'fecha_inspeccion' => $fecha,
                    'tipo' => $data['tipo'],
                    'kilometraje' => $data['kilometraje'] !== '' ? $data['kilometraje'] : null,
                    'resultado' => $data['resultado'],
                    'observaciones' => $data['observaciones'] ?: null,
                    'reportado_por' => $data['reportadoPor'] ?: null,
                    'origen' => 'bandeja_revision',
                    'estado' => 'validada',
                    'created_by' => auth()->id(),
                    'reviewed_by' => auth()->id(),
                ]);

                $directory = sprintf('monitoreo/inspecciones/%s/inspeccion-%d', $inspeccion->fecha_inspeccion->format('Y/m'), $inspeccion->id);
                foreach ($archivos->values() as $index => $archivo) {
                    $extension = strtolower(pathinfo($archivo->nombre_original, PATHINFO_EXTENSION) ?: 'jpg');
                    $destino = $directory.'/'.Str::uuid().'.'.$extension;
                    if (! Storage::disk($archivo->disk)->copy($archivo->path, $destino)) {
                        throw new \RuntimeException('No fue posible consolidar una imagen.');
                    }

                    $copiados[] = [$archivo->disk, $destino];
                    $originales[] = [$archivo->disk, $archivo->path];
                    $inspeccion->evidencias()->create([
                        'disk' => $archivo->disk,
                        'path' => $destino,
                        'nombre_original' => $archivo->nombre_original,
                        'mime_type' => $archivo->mime_type,
                        'size' => $archivo->size,
                        'sha256' => $archivo->sha256,
                        'orden' => $index + 1,
                        'clasificacion' => 'general',
                    ]);
                    $archivo->update(['path' => $destino]);
                }

                $this->caso->update([
                    'estado' => 'confirmado',
                    'unidad_confirmada_id' => $data['unidadId'],
                    'inspeccion_id' => $inspeccion->id,
                    'notas_revision' => $data['notasRevision'] ?: null,
                    'reviewed_by' => auth()->id(),
                    'confirmed_at' => now(),
                ]);
                $this->caso->mensajes()->where('incluido', true)->update(['estado' => 'utilizado']);

                return $inspeccion;
            });
        } catch (Throwable $exception) {
            foreach ($copiados as [$disk, $path]) {
                Storage::disk($disk)->delete($path);
            }
            report($exception);
            $this->addError('mensajes', 'No fue posible confirmar el caso. Inténtalo nuevamente.');

            return null;
        }

        foreach ($originales as [$disk, $path]) {
            Storage::disk($disk)->delete($path);
        }

        app(AuditLogger::class)->record(
            'Bandeja de evidencias',
            'Caso confirmado como inspección',
            $this->caso,
            [],
            ['inspeccion_id' => $inspeccion->id, 'unidad_id' => $inspeccion->unidad_id],
        );
        session()->flash('success', 'Caso confirmado e inspección creada correctamente.');

        return redirect()->route('inspecciones.detalle', $inspeccion);
    }

    public function render()
    {
        $unidades = Unidades::query()
            ->whereNotNull('placas')
            ->orderBy('placas')
            ->get(['id', 'placas', 'marca', 'modelo', 'estado_vehiculo'])
            ->filter(function (Unidades $unidad): bool {
                $placa = trim((string) $unidad->placas);

                return $unidad->is_activo
                    && $placa !== ''
                    && strlen($placa) <= 25
                    && preg_match('/[A-Za-z]/', $placa) === 1
                    && preg_match('/\d/', $placa) === 1
                    && preg_match('/^[A-Za-z0-9\s\-\/]+$/', $placa) === 1;
            })
            ->values();

        return view('livewire.inspeccion-revision-detalle', compact('unidades'))
            ->layout('layouts.app');
    }

    private function cargarCaso(): void
    {
        $this->caso->load(['mensajes.archivos', 'unidadSugerida', 'unidadConfirmada', 'inspeccion']);
    }
}
