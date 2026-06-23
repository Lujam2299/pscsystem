<div class="space-y-6">
    {{-- Header --}}

        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <h2 class="text-xl font-bold text-gray-800 dark:text-white">
                    <i class="ti ti-file-invoice mr-2 text-rose-500"></i>
                    Historial de Finiquitos
                </h2>
                <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">
                    Registros con cálculo de finiquito subido
                </p>
            </div>
            <div class="flex items-center gap-2">
                <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-rose-100 text-rose-700 dark:bg-rose-900/30 dark:text-rose-300">
                    <i class="ti ti-database mr-1"></i>
                    {{ $finiquitos->total() }} registros
                </span>
            </div>
        </div>

    {{-- Filtros --}}
        <form wire:submit.prevent="" class="grid grid-cols-1 md:grid-cols-4 gap-4">

            {{-- Búsqueda por nombre --}}
            <div class="md:col-span-2">
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                    <i class="ti ti-search mr-1"></i>Buscar empleado
                </label>
                <div class="relative">
                    <input
                        type="text"
                        wire:model.live.debounce.300ms="search"
                        placeholder="Nombre, apellido o email..."
                        class="w-full pl-10 pr-4 py-2.5 border border-gray-200 dark:border-gray-600 rounded-xl bg-gray-50 dark:bg-gray-700 text-gray-800 dark:text-white placeholder-gray-400 focus:ring-2 focus:ring-rose-500 focus:border-transparent transition"
                    >
                    <i class="ti ti-search absolute left-3 top-1/2 -translate-y-1/2 text-gray-400"></i>
                    @if($search)
                        <button
                            type="button"
                            wire:click="$set('search', '')"
                            class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600 dark:hover:text-gray-200"
                        >
                            <i class="ti ti-x"></i>
                        </button>
                    @endif
                </div>
            </div>

            {{-- Fecha inicio --}}
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                    <i class="ti ti-calendar mr-1"></i>Desde
                </label>
                <input
                    type="date"
                    wire:model.live="fecha_inicio"
                    max="{{ date('Y-m-d') }}"
                    class="w-full px-4 py-2.5 border border-gray-200 dark:border-gray-600 rounded-xl bg-gray-50 dark:bg-gray-700 text-gray-800 dark:text-white focus:ring-2 focus:ring-rose-500 focus:border-transparent transition"
                >
            </div>

            {{-- Fecha fin --}}
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                    <i class="ti ti-calendar mr-1"></i>Hasta
                </label>
                <input
                    type="date"
                    wire:model.live="fecha_fin"
                    max="{{ date('Y-m-d') }}"
                    class="w-full px-4 py-2.5 border border-gray-200 dark:border-gray-600 rounded-xl bg-gray-50 dark:bg-gray-700 text-gray-800 dark:text-white focus:ring-2 focus:ring-rose-500 focus:border-transparent transition"
                >
            </div>
        </form>

    {{-- Tabla de resultados --}}
    <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-100 dark:border-gray-700 shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-gray-50 dark:bg-gray-700/50 border-b border-gray-100 dark:border-gray-700">
                    <tr>
                        <th class="px-6 py-4 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Empleado</th>
                        <th class="px-6 py-4 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Fecha Baja</th>
                        <th class="px-6 py-4 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Tipo</th>
                        <th class="px-6 py-4 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Archivo</th>
                        <th class="px-6 py-4 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Monto</th>
                        <th class="px-6 py-4 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Acciones</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                    @forelse($finiquitos as $finiquito)
                        @php
                            // === Usuario y Foto ===
                            $user = $finiquito->usuario;
                            $documentacion = $user?->documentacionAltas;
                            $archFoto = is_iterable($documentacion) ? $documentacion->first()?->arch_foto : $documentacion?->arch_foto;
                            $fotoUrl = $this->getFotoUrl($archFoto);

                            // Nombre del usuario
                            if ($user) {
                                $nombre = $user->name ?? '';
                                $nombreCompleto = strtoupper(trim($nombre));
                            } else {
                                $nombreCompleto = 'SIN USUARIO';
                            }
                            $iniciales = Str::substr($nombreCompleto, 0, 2);

                            // === Archivo del Finiquito (¡ESTO FALTABA!) ===
                            $finiquitoPath = $finiquito->calculo_finiquito;
                            $fileUrl = route('finiquitos.archivo', $finiquito);
                            $fileIcon = $this->getFileIcon($finiquitoPath); // ← Icono según extensión
                            $fileExt = strtolower(pathinfo($finiquitoPath, PATHINFO_EXTENSION)); // ← Extensión para mostrar
                        @endphp
                        <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/50 transition-colors">
                            {{-- Empleado --}}
                            <td class="px-6 py-4">
                                <div class="flex items-center gap-3">
                                    {{-- Foto o Fallback con iniciales --}}
                                    <div class="w-10 h-10 rounded-full overflow-hidden bg-rose-100 dark:bg-rose-900/30 flex items-center justify-center border border-gray-200 dark:border-gray-700 flex-shrink-0">
                                        @if($fotoUrl)
                                            <img
                                                src="{{ $fotoUrl }}"
                                                alt="{{ $nombreCompleto }}"
                                                class="w-full h-full object-cover"
                                                onerror="this.parentElement.innerHTML='<span class=\'text-rose-600 dark:text-rose-400 font-semibold text-sm\'>{{ $iniciales }}</span>'"
                                                loading="lazy"
                                            >
                                        @else
                                            <span class="text-rose-600 dark:text-rose-400 font-semibold text-sm">
                                                {{ $iniciales }}
                                            </span>
                                        @endif
                                    </div>

                                    {{-- Nombre y email --}}
                                    <div class="min-w-0">
                                        <p class="font-medium text-gray-900 dark:text-white text-sm truncate">
                                            {{ $nombreCompleto }}
                                        </p>
                                    </div>
                                </div>
                            </td>

                            {{-- Fecha Baja --}}
                            <td class="px-6 py-4">
                                <span class="inline-flex items-center px-2.5 py-1 rounded-lg text-xs font-medium bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300">
                                    <i class="ti ti-calendar mr-1"></i>
                                    {{ \Carbon\Carbon::parse($finiquito->fecha_baja)->format('d/m/Y') }}
                                </span>
                            </td>

                            {{-- Tipo de Baja --}}
                            <td class="px-6 py-4">
                                <span class="inline-flex items-center px-2.5 py-1 rounded-lg text-xs font-medium
                                    {{ $finiquito->por === 'Renuncia' ? 'bg-amber-100 text-amber-700 dark:bg-amber-900/30 dark:text-amber-300' : 'bg-blue-100 text-blue-700 dark:bg-blue-900/30 dark:text-blue-300' }}">
                                    {{ $finiquito->por ?? 'N/A' }}
                                </span>
                            </td>

                            {{-- Archivo --}}
                            <td class="px-6 py-4">
                                @if($finiquito->calculo_finiquito)
                                    <div class="flex items-center gap-2">
                                        <i class="ti ti-{{ $fileIcon }} text-lg text-gray-500 dark:text-gray-400"></i>
                                        <span class="text-sm text-gray-700 dark:text-gray-300 uppercase">{{ $fileExt }}</span>
                                    </div>
                                @else
                                    <span class="text-sm text-gray-400">—</span>
                                @endif
                            </td>

                            {{-- Acciones --}}
                            <td class="px-6 py-4 text-sm font-semibold text-gray-800 dark:text-gray-200">
                                {{ $finiquito->finiquito ? '$'.number_format((float) $finiquito->finiquito->monto, 2) : '—' }}
                            </td>

                            {{-- Acciones --}}
                            <td class="px-6 py-4">
                                @if($finiquito->calculo_finiquito && $fileUrl)
                                    <a
                                        href="{{ $fileUrl }}"
                                        target="_blank"
                                        class="inline-flex items-center gap-1.5 px-3 py-1.5 text-sm font-medium text-rose-600 dark:text-rose-400 bg-rose-50 dark:bg-rose-900/20 hover:bg-rose-100 dark:hover:bg-rose-900/40 rounded-lg transition"
                                    >
                                        <i class="ti ti-eye"></i>
                                        Ver
                                    </a>
                                @else
                                    <span class="text-sm text-gray-400 cursor-not-allowed">No disponible</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-6 py-12 text-center">
                                <div class="flex flex-col items-center">
                                    <i class="ti ti-file-x text-4xl text-gray-300 dark:text-gray-600 mb-3"></i>
                                    <p class="text-gray-500 dark:text-gray-400 font-medium">No se encontraron registros</p>
                                    <p class="text-sm text-gray-400 dark:text-gray-500 mt-1">
                                        @if($search || $fecha_inicio || $fecha_fin)
                                            Intenta ajustar los filtros de búsqueda
                                        @else
                                            Aún no hay finiquitos subidos al sistema
                                        @endif
                                    </p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Paginación --}}
        <div class="px-6 py-4 border-t border-gray-100 dark:border-gray-700 bg-gray-50 dark:bg-gray-700/30">
            {{ $finiquitos->links() }}
        </div>
    </div>
    {{-- Estilos para personalizar paginación Livewire si es necesario --}}
@push('styles')
<style>
    .livewire-pagination {
        display: flex;
        gap: 4px;
        justify-content: center;
    }
    .livewire-pagination button,
    .livewire-pagination span {
        @apply px-3 py-1.5 text-sm rounded-lg border border-gray-200 dark:border-gray-600
               bg-white dark:bg-gray-800 text-gray-700 dark:text-gray-300
               hover:bg-gray-50 dark:hover:bg-gray-700 transition;
    }
    .livewire-pagination .active {
        @apply bg-rose-500 text-white border-rose-500 font-medium;
    }
    .livewire-pagination .disabled {
        @apply opacity-50 cursor-not-allowed;
    }
</style>
@endpush
</div>

