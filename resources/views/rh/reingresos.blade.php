<x-app-layout>
    <x-navbar></x-navbar>
    <div class="py-4 px-2 sm:py-6 sm:px-4">
        <div class="container mx-auto max-w-7xl">

            {{-- Botón comentado
            <div class="mb-6">
                <button id="btnProcesarReingresos" class="bg-blue-600 hover:bg-blue-700 text-white font-semibold py-2 px-4 rounded-lg shadow transition duration-200 ease-in-out transform hover:scale-105">
                    <span id="btnTextProcesar">Procesar Reingresos Pendientes</span>
                </button>
            </div>

            <div id="resultadoProceso" class="mt-4"></div>
            --}}

            <!-- Componente Livewire -->
            <div class="mt-4 mb-4">
                <livewire:reingreso-table />
            </div>


        </div>
    </div>

    <script>
        document.getElementById('btnProcesarReingresos').addEventListener('click', function() {
            const btn = this;
            const btnTextSpan = document.getElementById('btnTextProcesar');
            const resultadoDiv = document.getElementById('resultadoProceso');

            // Deshabilitar botón y cambiar texto mientras procesa
            btn.disabled = true;
            btn.classList.remove('bg-blue-600', 'hover:bg-blue-700');
            btn.classList.add('bg-gray-500', 'cursor-not-allowed');
            btnTextSpan.textContent = 'Procesando...';

            // Limpiar resultados anteriores
            resultadoDiv.innerHTML = '';

            // Realizar la petición Fetch
            fetch('{{ route("reingresos.procesar") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json'
                },
            })
            .then(response => {
                // Verificar si la respuesta es exitosa
                if (!response.ok) {
                    throw new Error(`HTTP error! Status: ${response.status}`);
                }
                return response.json();
            })
            .then(data => {
                // Mostrar mensaje de éxito
                resultadoDiv.innerHTML = `
                    <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative" role="alert">
                        <strong class="font-bold">Éxito! </strong>
                        <span class="block sm:inline">${data.mensaje}</span>
                        <span class="block sm:inline">Se crearon <strong>${data.registros_creados}</strong> registros nuevos.</span>
                    </div>
                `;
            })
            .catch(error => {
                console.error('Error al procesar reingresos:', error);
                // Mostrar mensaje de error
                resultadoDiv.innerHTML = `
                    <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative" role="alert">
                        <strong class="font-bold">Error! </strong>
                        <span class="block sm:inline">Ocurrió un error al procesar los reingresos. Consulte la consola.</span>
                    </div>
                `;
            })
            .finally(() => {
                // Restaurar el estado del botón
                btn.disabled = false;
                btn.classList.remove('bg-gray-500', 'cursor-not-allowed');
                btn.classList.add('bg-blue-600', 'hover:bg-blue-700');
                btnTextSpan.textContent = 'Procesar Reingresos Pendientes';
            });
        });
    </script>
</x-app-layout>
