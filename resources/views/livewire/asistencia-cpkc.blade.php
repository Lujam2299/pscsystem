<div class="container mx-auto px-4 py-6">
    <h2 class="text-xl font-bold mb-5 text-center">Registro de Asistencia CPKC</h2>
    <div class="bg-white shadow rounded-lg overflow-hidden">
        @foreach($puntos as $punto)
            <div class="border-b border-gray-200 p-4 last:border-0">
                <h3 class="font-semibold text-lg mb-2">{{ $punto }}</h3>

                <div x-data="{ open: false }" class="mb-3 relative">
                    <input
                        type="text"
                        wire:model.live="searches.{{ $punto }}"
                        placeholder="Buscar usuario..."
                        class="w-full border border-gray-300 rounded px-3 py-1.5 text-sm focus:outline-none focus:ring-1 focus:ring-blue-500"
                        @click="open = true"
                        @blur="setTimeout(() => open = false, 200)"
                    >
                    <div
                        x-show="open && searches['{{ $punto }}']?.length > 0"
                        class="absolute z-10 mt-1 w-full bg-white shadow-lg max-h-48 overflow-auto rounded border border-gray-200"
                    >
                        @foreach($users as $user)
                            @if(str_contains(strtolower($user->name), strtolower($searches[$punto] ?? '')))
                                <button
                                    type="button"
                                    wire:click="addUser('{{ $punto }}', {{ $user->id }})"
                                    class="block w-full text-left px-3 py-1.5 text-sm hover:bg-gray-100"
                                >
                                    {{ $user->name }}
                                </button>
                            @endif
                        @endforeach
                    </div>
                </div>

                @if(!empty($usersByPoint[$punto]))
                    <div class="mt-3 space-y-2">
                        <div class="flex justify-end gap-4 mb-1">
                            <span class="text-xs font-medium text-gray-500 w-16 text-center">Día</span>
                            <span class="text-xs font-medium text-gray-500 w-16 text-center">Noche</span>
                            <span class="w-6"></span>
                        </div>

                        @foreach($usersByPoint[$punto] as $userId => $userData)
                            <div class="flex items-center justify-between p-2 bg-gray-50 rounded">
                                <span class="text-sm font-medium flex-1">{{ $userData['user']->name }}</span>
                                <div class="flex items-center gap-3 ml-4">
                                    <select
                                        wire:change="setTurno('{{ $punto }}', {{ $userId }}, 'turno_dia', $event.target.value)"
                                        class="border border-gray-300 rounded px-2 py-1 text-sm w-16 text-center"
                                    >
                                        <option value="">—</option>
                                        <option value="A" {{ $userData['turno_dia'] === 'A' ? 'selected' : '' }}>A</option>
                                        <option value="D" {{ $userData['turno_dia'] === 'D' ? 'selected' : '' }}>D</option>
                                        <option value="F" {{ $userData['turno_dia'] === 'F' ? 'selected' : '' }}>F</option>
                                    </select>

                                    <select
                                        wire:change="setTurno('{{ $punto }}', {{ $userId }}, 'turno_noche', $event.target.value)"
                                        class="border border-gray-300 rounded px-2 py-1 text-sm w-16 text-center"
                                    >
                                        <option value="">—</option>
                                        <option value="A" {{ $userData['turno_noche'] === 'A' ? 'selected' : '' }}>A</option>
                                        <option value="D" {{ $userData['turno_noche'] === 'D' ? 'selected' : '' }}>D</option>
                                        <option value="F" {{ $userData['turno_noche'] === 'F' ? 'selected' : '' }}>F</option>
                                    </select>

                                    <button
                                        wire:click="removeUser('{{ $punto }}', {{ $userId }})"
                                        class="text-red-500 hover:text-red-700 text-sm w-6 h-6 flex items-center justify-center"
                                        title="Eliminar"
                                    >
                                        ✕
                                    </button>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <p class="text-gray-400 text-sm italic">No hay usuarios asignados.</p>
                @endif
            </div>
        @endforeach
    </div>

    <div class="mt-6 flex justify-end">
        <button
            wire:click="save"
            class="inline-flex items-center px-4 py-2 bg-green-600 hover:bg-green-700 text-white font-medium rounded-lg shadow-sm transition"
        >
            Guardar Asistencias
        </button>
    </div>
</div>
