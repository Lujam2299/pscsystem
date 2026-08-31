<x-livewire.monitoreo-layout :breadcrumb-items="[
        ['icon' => 'ti-home', 'url' => route('dashboard')],
        ['icon' => 'ti-car', 'url' => route('vehiculos.index'), 'label' => 'Control de Vehículos'],
        ['icon' => 'ti-id', 'label' => 'Detalle: ' . $unidad->placas]
    ]" title-main="Detalle de Vehículo" help-text="Información completa y estado actual del vehículo seleccionado">
  <div class="grid max-w-5xl grid-cols-1 gap-6 mx-auto md:grid-cols-3">
    <!-- Columna 1: Datos generales -->
    <div class="p-6 bg-white shadow-md rounded-xl dark:bg-gray-800">
      <div class="flex items-center gap-2 mb-3 text-base font-bold text-blue-700 dark:text-blue-300">
        <i class="text-lg ti ti-user"></i> Datos generales
      </div>
      <div class="space-y-2 text-gray-700 dark:text-gray-200">
        <div><span class="font-semibold">Propietario:</span> {{ $unidad->nombre_propietario }}</div>
        <div><span class="font-semibold">Zona:</span> {{ $unidad->zona }}</div>
        <div><span class="font-semibold">Punto:</span> {{ $unidad->asignacion_punto }}</div>
      </div>
    </div>
    <!-- Columna 2: Datos técnicos -->
    <div class="p-6 bg-white shadow-md rounded-xl dark:bg-gray-800">
      <div class="flex items-center gap-2 mb-3 text-base font-bold text-blue-700 dark:text-blue-300">
        <i class="text-lg ti ti-car"></i> Datos técnicos
      </div>
      <div class="space-y-2 text-gray-700 dark:text-gray-200">
        <div><span class="font-semibold">Marca:</span> {{ $unidad->marca }}</div>
        <div><span class="font-semibold">Modelo:</span> {{ $unidad->modelo }}</div>
        <div><span class="font-semibold">Kilometraje:</span> {{ is_numeric($unidad->kms) ? number_format($unidad->kms) :
          $unidad->kms }}</div>
      </div>
    </div>
    <!-- Columna 3: Placas y estado + Modal Alpine local -->
    <div x-data="{ open: false }"
      class="flex flex-col items-center justify-center p-6 bg-white shadow-md rounded-xl dark:bg-gray-800">
      <div class="flex items-center gap-2 mb-3 text-base font-bold text-blue-700 dark:text-blue-300">
        <i class="text-lg ti ti-id"></i> Placas
      </div>
      <div class="mb-3">
        <button id="openModalBtn" title="Ver historial de placas"
          class="inline-block px-4 py-2 font-mono text-xl font-bold tracking-widest text-gray-800 bg-white border border-blue-500 rounded shadow hover:bg-blue-50 focus:outline-none focus:ring-2 focus:ring-blue-400 select-none">
          {{ $unidad->placas }}
        </button>
      </div>
      <div>
        @if($unidad->is_activo)
        <span
          class="inline-flex items-center px-4 py-2 text-base font-semibold text-green-700 bg-green-100 rounded-full">
          <i class="mr-2 ti ti-circle-check"></i> Activo
        </span>
        @else
        <span class="inline-flex items-center px-4 py-2 text-base font-semibold text-red-700 bg-red-100 rounded-full">
          <i class="mr-2 ti ti-circle-x"></i> Inactivo
        </span>
        @endif
      </div>
      <!-- Modal historial placas JS puro -->
      <div id="modalPlacas" class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-40 hidden">
        <div class="bg-white dark:bg-gray-900 rounded-xl shadow-lg max-w-md w-full p-6 relative">
          <button type="button" id="closeModalBtn"
            class="absolute top-3 right-3 text-gray-500 hover:text-gray-700 dark:hover:text-gray-300" title="Cerrar">
            <i class="ti ti-x"></i>
          </button>
          <div class="mb-4 text-lg font-bold text-blue-700 dark:text-blue-300 flex items-center gap-2">
            <i class="ti ti-history"></i> Historial de placas
          </div>
          <ul class="space-y-3">
            @foreach($unidad->placas_historial as $placa)
            <li
              class="flex flex-col gap-1 p-3 bg-gray-50 dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700">
              <div class="flex items-center gap-2">
                <span
                  class="font-mono text-2xl font-extrabold text-blue-700 dark:text-blue-300 tracking-widest drop-shadow">{{
                  $placa->numero }}</span>
                @if($placa->estado === 'Activa')
                <span
                  class="inline-flex items-center px-2 py-1 text-xs font-semibold text-green-700 bg-green-100 rounded-full"><i
                    class="mr-1 ti ti-circle-check"></i> Activa</span>
                @else
                <span
                  class="inline-flex items-center px-2 py-1 text-xs font-semibold text-gray-600 bg-gray-200 rounded-full"><i
                    class="mr-1 ti ti-history"></i> Inactiva</span>
                @endif
              </div>
              <div class="text-xs text-gray-600 dark:text-gray-400">
                <span class="font-semibold">Asignada:</span> {{ $placa->fecha_asignacion ?
                \Carbon\Carbon::parse($placa->fecha_asignacion)->format('d/m/Y') : '-' }}
                @if($placa->fecha_baja)
                <span class="ml-2 font-semibold">Baja:</span> {{
                \Carbon\Carbon::parse($placa->fecha_baja)->format('d/m/Y') }}
                @endif
              </div>
            </li>
            @endforeach
            @if(empty($unidad->placas_historial))
            <li class="text-center text-gray-500 dark:text-gray-400">Sin historial de placas.</li>
            @endif
          </ul>
        </div>
      </div>
      <script>
        const openModalBtn = document.getElementById('openModalBtn');
        const closeModalBtn = document.getElementById('closeModalBtn');
        const modalPlacas = document.getElementById('modalPlacas');
        // Abrir modal
        openModalBtn.addEventListener('click', function() {
          modalPlacas.classList.remove('hidden');
        });
        // Cerrar modal por botón
        closeModalBtn.addEventListener('click', function() {
          modalPlacas.classList.add('hidden');
        });
        // Cerrar modal por click en fondo
        modalPlacas.addEventListener('click', function(e) {
          if (e.target === modalPlacas) {
            modalPlacas.classList.add('hidden');
          }
        });
      </script>
    </div>
  </div>
  <!-- Sección: Observaciones -->
  @if(!empty($unidad->observaciones))
  <div class="max-w-5xl p-6 mx-auto mt-8 shadow-md rounded-xl bg-gray-50 dark:bg-gray-900">
    <div class="flex items-center gap-2 mb-2 font-bold text-blue-700 dark:text-blue-300">
      <i class="text-lg ti ti-message-dots"></i>
      Observaciones
    </div>
    <div
      class="p-3 overflow-y-auto text-gray-800 bg-white border border-gray-200 rounded-lg dark:text-gray-200 max-h-40 dark:bg-gray-800 dark:border-gray-700">
      {!! nl2br(e($unidad->observaciones)) !!}
    </div>
  </div>
  @endif
  <!-- Sección: historial reciente de inspecciones -->
  <div class="max-w-5xl p-6 mx-auto mt-8 bg-white border border-gray-200 shadow-md rounded-xl dark:bg-gray-800 dark:border-gray-700">
    <div class="flex flex-col gap-3 mb-4 sm:flex-row sm:items-center sm:justify-between">
      <div>
        <div class="flex items-center gap-2 font-bold text-blue-700 dark:text-blue-300">
          <i class="text-lg ti ti-clipboard-check"></i> Inspecciones recientes
        </div>
        <p class="text-xs text-gray-500 dark:text-gray-400">Últimos registros de entrega, recepción o revisión.</p>
      </div>
      @can(\App\Support\Authorization\Permission::INSPECTIONS_VIEW)
        <a href="{{ route('inspecciones.index', ['unidad' => $unidad->id]) }}" class="text-sm font-semibold text-blue-600 hover:text-blue-800">Ver todas</a>
      @endcan
    </div>

    @can(\App\Support\Authorization\Permission::INSPECTIONS_VIEW)
      <div class="space-y-3">
        @forelse($unidad->inspecciones as $inspeccion)
          <a href="{{ route('inspecciones.detalle', $inspeccion) }}"
            class="flex flex-col gap-2 p-3 border border-gray-200 rounded-lg hover:bg-blue-50 dark:border-gray-700 dark:hover:bg-gray-700 sm:flex-row sm:items-center sm:justify-between">
            <div>
              <span class="font-semibold text-gray-900 dark:text-white">{{ $inspeccion->fecha_inspeccion->format('d/m/Y H:i') }}</span>
              <span class="ml-2 text-sm text-gray-500 dark:text-gray-400">{{ str($inspeccion->tipo)->replace('_', ' ')->title() }}</span>
            </div>
            <div class="text-sm text-gray-600 dark:text-gray-300">
              {{ str($inspeccion->resultado)->replace('_', ' ')->title() }} · {{ $inspeccion->evidencias_count }} evidencia(s)
            </div>
          </a>
        @empty
          <p class="py-4 text-sm text-center text-gray-500 dark:text-gray-400">Esta unidad todavía no tiene inspecciones registradas.</p>
        @endforelse
      </div>
    @else
      <p class="text-sm text-gray-500 dark:text-gray-400">No tienes permiso para consultar las inspecciones.</p>
    @endcan
  </div>
  <!-- Botones de acción -->
  <div class="flex justify-end max-w-5xl gap-4 mx-auto mt-8">
    <a href="{{ route('vehiculos.index', ['editar' => $unidad->id, 'return' => 'detalle']) }}"
      class="flex items-center gap-2 px-5 py-2 text-white bg-blue-400 rounded-lg shadow hover:bg-blue-500"
      title="Editar">
      <i class="ti ti-edit"></i> Editar
    </a>
    <a href="{{ route('vehiculos.index') }}"
      class="flex items-center gap-2 px-5 py-2 text-gray-700 bg-gray-100 border border-gray-300 rounded-lg shadow hover:bg-gray-200"
      title="Regresar">
      <i class="ti ti-arrow-left"></i> Regresar
    </a>
  </div>
</x-livewire.monitoreo-layout>
