<?php

namespace App\Livewire;

use App\Models\InspeccionEvidencia;
use App\Models\InspeccionMensaje;
use App\Models\InspeccionMensajeArchivo;
use App\Models\InspeccionRevisionCaso;
use App\Services\AuditLogger;
use App\Services\InspeccionMensajeAgrupador;
use App\Support\Authorization\Permission;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Livewire\Component;
use Livewire\WithFileUploads;
use Livewire\WithPagination;
use Throwable;

class InspeccionRecepcionBandeja extends Component
{
    use WithFileUploads;
    use WithPagination;

    public string $filtroEstado = '';

    public bool $mostrarImportador = false;

    public string $conversacion = 'Reportes de Reparaciones';

    public string $remitente = '';

    public string $fechaBase = '';

    public string $textoAnterior = '';

    public string $textoImagenes = '';

    public string $textoPosterior = '';

    public array $imagenes = [];

    public function mount(): void
    {
        Gate::authorize(Permission::INSPECTION_INBOX_VIEW);
        $this->fechaBase = now()->format('Y-m-d\TH:i');
    }

    public function abrirImportador(): void
    {
        Gate::authorize(Permission::INSPECTION_INBOX_MANAGE);
        $this->resetValidation();
        $this->mostrarImportador = true;
    }

    public function cancelarImportador(): void
    {
        $this->resetImportador();
    }

    public function importar(InspeccionMensajeAgrupador $agrupador): mixed
    {
        Gate::authorize(Permission::INSPECTION_INBOX_MANAGE);

        $data = $this->validate([
            'conversacion' => ['required', 'string', 'max:255'],
            'remitente' => ['nullable', 'string', 'max:255'],
            'fechaBase' => ['required', 'date'],
            'textoAnterior' => ['nullable', 'string', 'max:5000'],
            'textoImagenes' => ['nullable', 'string', 'max:5000'],
            'textoPosterior' => ['nullable', 'string', 'max:5000'],
            'imagenes' => ['required', 'array', 'min:1', 'max:20'],
            'imagenes.*' => ['required', 'image', 'mimes:jpg,jpeg,png,webp', 'max:10240'],
        ]);

        $hashes = collect($this->imagenes)->map(fn ($imagen): string => hash_file('sha256', $imagen->getRealPath()));
        if ($hashes->duplicates()->isNotEmpty()) {
            $this->addError('imagenes', 'La selección contiene imágenes duplicadas.');

            return null;
        }

        if (InspeccionMensajeArchivo::query()->whereIn('sha256', $hashes)->exists()
            || InspeccionEvidencia::query()->whereIn('sha256', $hashes)->exists()) {
            $this->addError('imagenes', 'Una o más imágenes ya fueron recibidas anteriormente.');

            return null;
        }

        $storedPaths = [];

        try {
            $caso = DB::transaction(function () use ($data, $hashes, &$storedPaths): InspeccionRevisionCaso {
                $caso = InspeccionRevisionCaso::create([
                    'estado' => 'pendiente',
                    'created_by' => auth()->id(),
                ]);

                $fecha = Carbon::parse($data['fechaBase']);
                $this->crearMensajeTexto($caso, $data['textoAnterior'], $fecha->copy()->subMinute());

                $mensajeImagenes = InspeccionMensaje::create([
                    'caso_id' => $caso->id,
                    'origen' => 'manual',
                    'conversacion' => $data['conversacion'],
                    'remitente' => $data['remitente'] ?: null,
                    'fecha_mensaje' => $fecha,
                    'tipo' => 'imagenes',
                    'texto' => $data['textoImagenes'] ?: null,
                    'incluido' => true,
                    'created_by' => auth()->id(),
                ]);

                $directory = 'monitoreo/inspecciones-temporales/caso-'.$caso->id;
                foreach ($this->imagenes as $index => $imagen) {
                    $extension = strtolower($imagen->getClientOriginalExtension() ?: 'jpg');
                    $path = $imagen->storeAs($directory, Str::uuid().'.'.$extension, 'local');
                    if ($path === false) {
                        throw new \RuntimeException('No fue posible guardar una imagen temporal.');
                    }

                    $storedPaths[] = $path;
                    $mensajeImagenes->archivos()->create([
                        'disk' => 'local',
                        'path' => $path,
                        'nombre_original' => $imagen->getClientOriginalName(),
                        'mime_type' => $imagen->getMimeType() ?: 'application/octet-stream',
                        'size' => $imagen->getSize(),
                        'sha256' => $hashes[$index],
                        'orden' => $index + 1,
                    ]);
                }

                $this->crearMensajeTexto($caso, $data['textoPosterior'], $fecha->copy()->addMinute());

                return $caso;
            });
        } catch (Throwable $exception) {
            foreach ($storedPaths as $path) {
                Storage::disk('local')->delete($path);
            }
            report($exception);
            $this->addError('imagenes', 'No fue posible importar el caso. Inténtalo nuevamente.');

            return null;
        }

        $agrupador->analizar($caso);
        app(AuditLogger::class)->record(
            'Bandeja de evidencias',
            'Caso importado manualmente',
            $caso,
            [],
            ['mensajes' => $caso->mensajes()->count(), 'imagenes' => count($storedPaths)],
        );

        $this->resetImportador();

        return redirect()->route('inspecciones.recepcion.detalle', $caso);
    }

    public function render()
    {
        $casos = InspeccionRevisionCaso::query()
            ->with(['unidadSugerida:id,placas,marca,modelo', 'unidadConfirmada:id,placas,marca,modelo'])
            ->withCount(['mensajes', 'mensajes as archivos_count' => fn ($query) => $query->whereHas('archivos')])
            ->when($this->filtroEstado, fn ($query) => $query->where('estado', $this->filtroEstado))
            ->latest()
            ->paginate(12);

        return view('livewire.inspeccion-recepcion-bandeja', compact('casos'))
            ->layout('layouts.app');
    }

    private function crearMensajeTexto(InspeccionRevisionCaso $caso, ?string $texto, $fecha): void
    {
        if (blank($texto)) {
            return;
        }

        InspeccionMensaje::create([
            'caso_id' => $caso->id,
            'origen' => 'manual',
            'conversacion' => $this->conversacion,
            'remitente' => $this->remitente ?: null,
            'fecha_mensaje' => $fecha,
            'tipo' => 'texto',
            'texto' => $texto,
            'incluido' => true,
            'created_by' => auth()->id(),
        ]);
    }

    private function resetImportador(): void
    {
        $this->resetValidation();
        $this->reset(['remitente', 'textoAnterior', 'textoImagenes', 'textoPosterior', 'imagenes']);
        $this->conversacion = 'Reportes de Reparaciones';
        $this->fechaBase = now()->format('Y-m-d\TH:i');
        $this->mostrarImportador = false;
    }
}
