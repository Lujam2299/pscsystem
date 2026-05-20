<?php

namespace App\Livewire;

use App\Models\SolicitudBajas;
use App\Models\User;
use Livewire\Component;
use Livewire\WithPagination;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;

class HistorialFiniquitosNominas extends Component
{
    use WithPagination;

    public $search = '';
    public $fecha_inicio;
    public $fecha_fin;
    public $perPage = 10;

    protected $queryString = [
        'search' => ['except' => ''],
        'fecha_inicio' => ['except' => ''],
        'fecha_fin' => ['except' => ''],
        'page' => ['except' => 1],
    ];

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function updatingFechaInicio()
    {
        $this->resetPage();
    }

    public function updatingFechaFin()
    {
        $this->resetPage();
    }

    public function clearFilters()
    {
        $this->reset(['search', 'fecha_inicio', 'fecha_fin']);
        $this->resetPage();
    }

    public function render()
    {
        $query = SolicitudBajas::with('usuario.documentacionAltas')
            ->whereNotNull('calculo_finiquito')
            /*->where('estatus', 'Aceptada')*/;

        // Filtro por nombre
        if (!empty($this->search)) {
            $query->whereHas('usuario.documentacionAltas', function ($q) {
                $q->whereRaw('name', 'like', ["%{$this->search}%"]);
            });
        }

        // Filtro por rango de fechas (fecha_baja)
        if (!empty($this->fecha_inicio)) {
            $query->whereDate('fecha_baja', '>=', $this->fecha_inicio);
        }
        if (!empty($this->fecha_fin)) {
            $query->whereDate('fecha_baja', '<=', $this->fecha_fin);
        }

        $finiquitos = $query->orderBy('fecha_baja', 'desc')
            ->paginate($this->perPage);

        return view('livewire.historial-finiquitos-nominas', [
            'finiquitos' => $finiquitos
        ]);
    }

    // Método para obtener la URL del archivo
    public function getFileUrl($path)
    {
        if (empty($path)) return null;

        // Si es ruta relativa a storage/app/public
        if (str_starts_with($path, 'solicitudesBajas/') ||
            str_starts_with($path, 'finiquitos/') ||
            !filter_var($path, FILTER_VALIDATE_URL)) {

            $cleanPath = ltrim($path, '/');

            // Generar URL con puerto forzado para entorno local
            if (app()->environment('local')) {
                $baseUrl = config('app.url');
                // Si APP_URL no tiene puerto, agregar :8000
                if (!preg_match('/:\d+$/', $baseUrl) && str_contains($baseUrl, 'localhost')) {
                    $baseUrl = rtrim($baseUrl, '/') . ':8000';
                }
                return $baseUrl . '/storage/' . $cleanPath;
            }

            // Para producción, usar Storage normal
            try {
                return Storage::disk('public')->url($cleanPath);
            } catch (\Exception $e) {
                return asset('storage/' . $cleanPath);
            }
        }

        // Si ya es URL completa, retornarla tal cual
        if (filter_var($path, FILTER_VALIDATE_URL)) {
            return $path;
        }

        return null;
    }

    // Método para obtener el icono según extensión
    public function getFileIcon($path)
    {
        if (empty($path)) return 'file';

        $extension = strtolower(pathinfo($path, PATHINFO_EXTENSION));

        return match($extension) {
            'pdf' => 'file-type-pdf',
            'xls', 'xlsx' => 'file-type-xls',
            'jpg', 'jpeg', 'png', 'gif', 'webp' => 'photo',
            default => 'file',
        };
    }

    // Método para descargar/ver archivo
    public function verArchivo($path, $nombre)
    {
        $url = $this->getFileUrl($path);

        // Redirección segura para archivos en storage
        if ($url) {
            return redirect()->to($url);
        }

        $this->dispatch('notify', message: 'Archivo no disponible', type: 'error');
    }

    // Método para obtener la URL de la foto del usuario
    public function getFotoUrl($archFoto)
    {
        if (empty($archFoto)) {
            return null;
        }

        $path = $archFoto;

        // Si la ruta ya incluye 'storage/', quitarlo para evitar duplicados
        if (str_starts_with($path, 'storage/')) {
            $path = substr($path, 8); // Remover 'storage/'
        }

        // Si la ruta ya incluye 'public/', quitarlo también
        if (str_starts_with($path, 'public/')) {
            $path = substr($path, 7); // Remover 'public/'
        }

        $cleanPath = ltrim($path, '/');

        // Si ya es URL completa (http://...), retornarla tal cual
        if (filter_var($archFoto, FILTER_VALIDATE_URL)) {
            return $archFoto;
        }

        // Forzar puerto en entorno local
        if (app()->environment('local')) {
            $baseUrl = config('app.url');
            if (!preg_match('/:\d+$/', $baseUrl) && str_contains($baseUrl, 'localhost')) {
                $baseUrl = rtrim($baseUrl, '/') . ':8000';
            }
            return $baseUrl . '/storage/' . $cleanPath;
        }

        // Para producción, usar Storage
        try {
            return Storage::disk('public')->url($cleanPath);
        } catch (\Exception $e) {
            return asset('storage/' . $cleanPath);
        }
    }

    // Método para obtener iniciales como fallback
    public function getIniciales($user)
    {
        if (!$user) return 'SU';

        $nombre = trim(($user->nombre ?? '') . ' ' . ($user->apellido_paterno ?? ''));
        $partes = preg_split('/\s+/', strtoupper($nombre));

        if (count($partes) >= 2) {
            return substr($partes[0], 0, 1) . substr($partes[1], 0, 1);
        }

        return strtoupper(substr($nombre, 0, 2)) ?: 'US';
    }
}
