<div class="bg-white min-h-screen p-6 rounded-lg shadow-sm">
    <div class="container mx-auto p-4">
        <h1 class="text-2xl6">Gestión de Turnos</h1>
    </div>

    <div class="bg-white rounded-lg shadow p-4 mb-6">
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <div>
                <label class="block text-sm font-medium00">Punto</label>
                <select wire:model.live="subpunto_id" class="w-full border rounded px-2 py-1 border-gray-300 focus:ring-blue-500 focus:border-blue-500">
                    <option value="">-- Todos --</option>
                    @foreach($puntos as $punto)
                        <option value="{{ $punto->id }}">{{ $punto->nombre }}</option>
                    @endforeach
                </select>
            </div>

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
                <label class="block text-sm font-medium text-gray-700">Días atrás</label>
                <input
                    type="number"
                    wire:model.live="dias_atras"
                    min="1"
                    max="365"
                    class="w-full border rounded px-2 py-1 border-gray-300 focus:ring-blue-500 focus:border-blue-500"
                />
            </div>
        </div>
    </div>

    <!-- Tabla -->
    <div class="overflow-x-auto bg-white rounded-lg shadow">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Fecha</th>
                    <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Usuario</th>
                    <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tipo</th>
                    <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Hora</th>
                    <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">KM</th>
                    <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Rayas</th>
                    <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Placa</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                @foreach($registros as $i => $r)
                    <tr class="hover:bg-gray-50">
                        <td class="px-4 py-2">
                            <input type="date" wire:model="registros.{{ $i }}.fecha" class="w-full border rounded px-2 py-1" {{ $r['id'] ? 'readonly' : '' }}>
                        </td>
                        <td class="px-4 py-2">
                            @if($r['id'])
                                <span class="block">{{ $r['nombre_elemento'] }}</span>
                            @else
                                <div x-data="userAutocomplete({{ $i }}, '{{ $r['nombre_elemento'] ?? '' }}')" x-init="init()">
                                    <input
                                        type="text"
                                        x-model="term"
                                        @input="debouncedFetchSuggestions"
                                        @keydown.arrow-down.prevent="highlightNext()"
                                        @keydown.arrow-up.prevent="highlightPrev()"
                                        @keydown.enter.prevent="selectHighlighted()"
                                        class="w-full border rounded px-2 py-1"
                                        placeholder="Buscar usuario..."
                                    />
                                    <ul x-show="isOpen" class="absolute bg-white border rounded shadow mt-1 z-10 max-h-40 overflow-y-auto w-64">
                                        <template x-for="(u, index) in suggestions" :key="index">
                                            <li
                                                x-text="u.name"
                                                class="px-3 py-1 hover:bg-gray-100 cursor-pointer text-gray-700"
                                                :class="{ 'bg-blue-100': index === highlightedIndex }"
                                                @click="selectSuggestion(u)"
                                            ></li>
                                        </template>
                                    </ul>
                                </div>
                            @endif
                        </td>
                        <td class="px-4 py-2">
                            <input type="text" wire:model="registros.{{ $i }}.tipo" class="w-full border rounded px-2 py-1" {{ $r['id'] ? 'readonly' : '' }}>
                        </td>
                        <td class="px-4 py-2">
                            <input type="time" wire:model="registros.{{ $i }}.hora_inicio" class="w-full border rounded px-2 py-1" {{ $r['id'] ? 'readonly' : '' }}>
                        </td>
                        <td class="px-4 py-2">
                            <input type="number" step="0.01" wire:model="registros.{{ $i }}.km_inicio" class="w-full border rounded px-2 py-1" {{ $r['id'] ? 'readonly' : '' }}>
                        </td>
                        <td class="px-4 py-2">
                            <input type="number" step="0.01" wire:model="registros.{{ $i }}.rayas_inicio" class="w-full border rounded px-2 py-1" {{ $r['id'] ? 'readonly' : '' }}>
                        </td>
                        <td class="px-4 py-2">
                            <input type="text" wire:model="registros.{{ $i }}.placas" class="w-full border rounded px-2 py-1" {{ $r['id'] ? 'readonly' : '' }}>
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
        <strong class="text-blue-800">Rendimiento: </strong>
        <span class="text-blue-900 font-medium">{{ $total_litros > 0 ? number_format($total_km / $total_litros, 2) : 'N/A' }} km/L</span>
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
                console.error("Error fetching suggestions:", error);
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

function userAutocomplete(rowIndex, initialValue) {
    return {
        term: initialValue || '',
        suggestions: [],
        isOpen: false,
        highlightedIndex: -1,
        debounceTimer: null,

        init() {
            $watch('term', value => {
                @this.set(`registros.${rowIndex}.nombre_elemento`, value);
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
                const response = await fetch(`/api/users?q=${encodeURIComponent(this.term)}`);
                const data = await response.json();
                this.suggestions = data.users || [];
                this.isOpen = this.suggestions.length > 0;
                this.highlightedIndex = -1;
            } catch (error) {
                console.error("Error fetching user suggestions:", error);
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
        selectSuggestion(user) {
            this.term = user.name;
            this.isOpen = false;
            this.highlightedIndex = -1;
            @this.set(`registros.${rowIndex}.nombre_elemento`, user.name);
            @this.set(`registros.${rowIndex}.user_id`, user.id);
        }
    };
}
</script>
