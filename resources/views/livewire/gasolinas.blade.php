<div class="bg-white min-h-screen p-6 rounded-lg shadow-sm mb-4 mt-4">
    <div class="container mx-auto p-4">
        <h1 class="text-2xl font-bold mb-6">Gestión de Turnos y Gasolina</h1>
    </div>

    <div class="bg-white rounded-lg shadow p-4 mb-6">
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <div x-data="placaAutocomplete()" x-init="init()">
                <label class="block text-sm font-medium text-gray-700">Placa</label>
                <input
                    type="text"
                    x-model="term"
                    @input="debouncedFetchSuggestions"
                    @keydown.arrow-down.prevent="highlightNext()"
                    @keydown.arrow-up.prevent="highlightPrev()"
                    @keydown.enter.prevent="selectHighlighted()"
                    class="w-full border rounded px-2 py-1 border-gray-300 focus:ring-blue-500 focus:border-blue-500"
                    placeholder="PS3975A"
                />
                <ul x-show="isOpen" class="absolute bg-white border rounded shadow mt-1 z-10 max-h-40 overflow-y-auto w-64">
                    <template x-for="(s, index) in suggestions" :key="index">
                        <li
                            x-text="s"
                            class="px-3 py-1 hover:bg-gray-100 cursor-pointer text-gray-700"
                            :class="{ 'bg-blue-100': index === highlightedIndex }"
                            @click="selectSuggestion(s)"
                        ></li>
                    </template>
                </ul>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700">Rango de Fechas</label>
                <div class="grid grid-cols-2 gap-2">
                    <input
                        type="date"
                        wire:model.live="fecha_desde"
                        class="w-full border rounded px-2 py-1 border-gray-300 focus:ring-blue-500 focus:border-blue-500"
                    />
                    <input
                        type="date"
                        wire:model.live="fecha_hasta"
                        class="w-full border rounded px-2 py-1 border-gray-300 focus:ring-blue-500 focus:border-blue-500"
                    />
                </div>
                <div class="text-xs text-gray-500 mt-1">
                    Desde: {{ $fecha_desde ?? 'No definido' }} | Hasta: {{ $fecha_hasta ?? 'Hoy' }}
                </div>
            </div>
        </div>
    </div>

    <!-- Tabla -->
    <div class="overflow-x-auto bg-white rounded-lg shadow">
        <table class="min-w-full divide-y divide-gray-200 table-fixed">
            <thead class="bg-gray-50 text-xs font-medium text-gray-500 uppercase tracking-wider">
                <tr>
                    <th colspan="7" class="px-1 py-2 text-center bg-blue-50">Turno</th>
                    <th colspan="4" class="px-1 py-2 text-center bg-green-50">Antes de carga</th>
                    <th colspan="2" class="px-1 py-2 text-center bg-yellow-50">Gasolina</th>
                    <th colspan="1" class="px-1 py-2 text-center bg-purple-50">Después de carga</th>
                </tr>
                <tr>
                    <th class="px-1 py-2 w-12">Fecha</th>
                    <th class="px-1 py-2 w-30">Usuario</th>
                    <th class="px-1 py-2 w-16">Tipo</th>
                    <th class="px-1 py-2 w-14">Hora</th>
                    <th class="px-1 py-2 w-20">KM</th>
                    <th class="px-1 py-2 w-10">Rayas</th>
                    <th class="px-1 py-2 w-20">Placa</th>
                    <th class="px-1 py-2 w-18">Hora Carga</th>
                    <th class="px-1 py-2 w-12">Rayas (Antes)</th>
                    <th class="px-1 py-2 w-20">KM Carga</th>
                    <th class="px-1 py-2 w-16">KMR Entre Cargas</th>
                    <th class="px-1 py-2 w-16">Dinero $</th>
                    <th class="px-1 py-2 w-10">Litros</th>
                    <th class="px-1 py-2 w-12">Rayas (Desp.)</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200 text-sm">
                @foreach($registros as $i => $r)
                    <tr class="hover:bg-gray-50" wire:key="row-{{ $i }}">
                        <!-- Turnos -->
                        <td class="px-1 py-1.5">
                            <input type="date" wire:model="registros.{{ $i }}.fecha"
                                   class="w-4/5 min-h-[32px] px-1.5 py-1 text-xs border rounded border-gray-300 focus:ring-blue-500 focus:border-blue-500">
                        </td>
                        <td class="px-1 py-1.5">
                            <div class="relative">
                                <input
                                    type="text"
                                    wire:model="registros.{{ $i }}.nombre_elemento"

                                    class="w-full min-h-[32px] px-1.5 py-1 text-xs border rounded border-gray-300 focus:ring-blue-500 focus:border-blue-500 cursor-pointer"
                                    placeholder="Buscar usuario..."
                                    wire:click="openUserModal({{ $i }})"
                                />
                            </div>

                            <!-- Modal -->
                            @if($showUserModal && $currentRowIndex === $i)
                                <div class="absolute z-50 bg-white border rounded shadow-lg w-40 mt-1">
                                    <input
                                        type="text"
                                        wire:model.live="userSearchQuery"
                                        wire:keydown.escape="$set('showUserModal', false)"
                                        class="w-full px-2 py-1 text-xs border-b border-gray-200 focus:outline-none"
                                        placeholder="Buscar..."
                                    />
                                    <ul class="max-h-40 overflow-y-auto">
                                        @foreach($usersSuggestion as $u)
                                            <li
                                                class="px-2 py-1.5 text-xs hover:bg-gray-100 cursor-pointer"
                                                wire:click="selectUser({{ $u['id'] }}, '{{ $u['name'] }}')"
                                            >
                                                {{ $u['name'] }}
                                            </li>
                                        @endforeach
                                        @if(empty($usersSuggestion))
                                            <li class="px-2 py-1.5 text-xs text-gray-500">No hay coincidencias</li>
                                        @endif
                                    </ul>
                                    <div class="p-1 text-right">
                                        <button
                                            wire:click="$set('showUserModal', false)"
                                            class="text-xs text-red-500 hover:text-red-700"
                                        >
                                            Cerrar
                                        </button>
                                    </div>
                                </div>
                            @endif
                        </td>
                        <td class="px-1 py-1.5">
                            <input type="text" wire:model="registros.{{ $i }}.tipo"
                                   class="w-full min-h-[32px] px-1.5 py-1 text-xs border rounded border-gray-300 focus:ring-blue-500 focus:border-blue-500">
                        </td>
                        <td class="px-1 py-1.5">
                            <input type="time" wire:model="registros.{{ $i }}.hora_inicio"
                                   class="w-17 min-h-[32px] px-1 py-1 text-xs border rounded border-gray-300 focus:ring-blue-500 focus:border-blue-500">
                        </td>
                        <td class="px-1 py-1.5">
                            <input type="number" step="1" wire:model="registros.{{ $i }}.km_inicio"
                                   class="w-full min-h-[32px] px-0.5 py-1 text-xs border rounded border-gray-300 focus:ring-blue-500 focus:border-blue-500">
                        </td>
                        <td class="px-1 py-1.5">
                            <input type="number" step="0.5" wire:model="registros.{{ $i }}.rayas_inicio"
                                   class="w-16 min-h-[32px] px-0.5 py-1 text-xs border rounded border-gray-300 focus:ring-blue-500 focus:border-blue-500">
                        </td>
                        <td class="px-1 py-1.5">
                            <input type="text" wire:model="registros.{{ $i }}.placas"
                                   class="w-full min-h-[32px] px-1.5 py-1 text-xs border rounded border-gray-300 focus:ring-blue-500 focus:border-blue-500">
                        </td>

                        <!-- Carga -->
                        <td class="px-1 py-1.5">
                            <input type="time" wire:model="registros.{{ $i }}.hora_carga"
                                   class="w-21 min-h-[32px] px-1.5 py-1 text-xs border rounded border-gray-300 focus:ring-blue-500 focus:border-blue-500">
                        </td>
                        <td class="px-1 py-1.5">
                            <input type="number" step="0.5" wire:model="registros.{{ $i }}.gasolina_antes_carga"
                                   class="w-16 min-h-[32px] px-1.5 py-1 text-xs border rounded border-gray-300 focus:ring-blue-500 focus:border-blue-500">
                        </td>
                        <td class="px-1 py-1.5">
                            <input type="number" step="0.01" wire:model="registros.{{ $i }}.km_carga"
                                   class="w-full min-h-[32px] px-1.5 py-1 text-xs border rounded border-gray-300 focus:ring-blue-500 focus:border-blue-500">
                        </td>
                        <td class="px-1 py-1.5">
                            <span class="block text-xs truncate">{{ number_format($r['kmr_entre_cargas']) }}</span>
                        </td>
                        <td class="px-1 py-1.5">
                            <input type="number" step="0.01" wire:model="registros.{{ $i }}.monto"
                                   class="w-full min-h-[32px] px-1.5 py-1 text-xs border rounded border-gray-300 focus:ring-blue-500 focus:border-blue-500">
                        </td>
                        <td class="px-1 py-1.5">
                            <input type="number" step="0.5" wire:model="registros.{{ $i }}.litros"
                                   class="w-10 min-h-[32px] px-1.5 py-1 text-xs border rounded border-gray-300 focus:ring-blue-500 focus:border-blue-500">
                        </td>
                        <td class="px-1 py-1.5">
                            <input type="number" step="0.5" wire:model="registros.{{ $i }}.gasolina_despues_carga"
                                   class="w-16 min-h-[32px] px-1.5 py-1 text-xs border rounded border-gray-300 focus:ring-blue-500 focus:border-blue-500">
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <div class="my-4 flex space-x-4">
        <button
            wire:click="addRow"
            {{ empty($placa) ? 'disabled' : '' }}
            class="bg-blue-500 text-white px-4 py-2 rounded hover:bg-blue-600 transition flex items-center gap-2 {{ empty($placa) ? 'opacity-50 cursor-not-allowed' : '' }}"
        >
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                <path fill-rule="evenodd" d="M10 5a1 1 0 011 1v3h3a1 1 0 110 2h-3v3a1 1 0 11-2 0v-3H6a1 1 0 110-2h3V6a1 1 0 011-1z" clip-rule="evenodd" />
            </svg>
            Agregar Fila
        </button>
        <button
            wire:click="guardarTodos"
            {{ empty($placa) ? 'disabled' : '' }}
            class="bg-green-500 text-white px-4 py-2 rounded hover:bg-green-600 transition flex items-center gap-2 {{ empty($placa) ? 'opacity-50 cursor-not-allowed' : '' }}"
        >
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" />
            </svg>
            Guardar Todos
        </button>
    </div>

    <div class="mt-4 p-4 bg-blue-50 rounded-lg border border-blue-100">
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 text-sm">
            <div class="bg-white p-3 rounded border">
                <span class="text-gray-600">Dif. KM</span><br>
                <span class="font-bold text-lg">{{ number_format($total_km, 0) }} km</span>
            </div>
            <div class="bg-white p-3 rounded border">
                <span class="text-gray-6">Gasto Total</span><br>
                <span class="font-bold text-lg">${{ number_format($total_dinero, 2) }}</span>
            </div>
            <div class="bg-white p-3 rounded border">
                <span class="text-gray-600">Litros</span><br>
                <span class="font-bold text-lg">{{ number_format($total_litros, 2) }} L</span>
            </div>
        </div>
        <div class="mt-3 pt-3 border-t border-gray-200">
            <div class="flex justify-between items-center">
                <span class="text-blue-800 font-semibold">Rendimiento:</span>
                <span class="text-yellow-600 font-bold text-xl">
                    @if($total_litros > 0)
                        {{ number_format($rendimiento, 2) }} km/L
                    @else
                        N/A
                    @endif
                </span>
            </div>
        </div>
    </div>
</div>

<script>
function placaAutocomplete() {
    return {
        term: '',
        suggestions: [],
        isOpen: false,
        highlightedIndex: -1,
        debounceTimer: null,

        init() {
            this.term = @js($placa);
            $watch('term', value => {
                @this.set('placa', value);
            });
        },

        debouncedFetchSuggestions() {
            clearTimeout(this.debounceTimer);
            this.debounceTimer = setTimeout(() => {
                this.fetchSuggestions();
            }, 300);
        },

        async fetchSuggestions() {
            if (this.term.length < 2) {
                this.suggestions = [];
                this.isOpen = false;
                return;
            }

            try {
                const response = await fetch(`/api/placas?q=${encodeURIComponent(this.term)}`);
                const data = await response.json();
                this.suggestions = data.placas || [];
                this.isOpen = this.suggestions.length > 0;
                this.highlightedIndex = -1;
            } catch (error) {
                console.error("Error:", error);
            }
        },

        highlightNext() {
            if (this.highlightedIndex < this.suggestions.length - 1) {
                this.highlightedIndex++;
            }
        },
        highlightPrev() {
            if (this.highlightedIndex > 0) {
                this.highlightedIndex--;
            }
        },
        selectHighlighted() {
            if (this.highlightedIndex >= 0 && this.suggestions[this.highlightedIndex]) {
                this.selectSuggestion(this.suggestions[this.highlightedIndex]);
            }
        },
        selectSuggestion(placa) {
            this.term = placa;
            this.isOpen = false;
            this.highlightedIndex = -1;
            @this.set('placa', placa);
        }
    };
}
</script>
