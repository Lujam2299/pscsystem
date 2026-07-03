@props(['selected' => []])

@php
    $selectedIds = collect($selected)
        ->filter(fn ($id) => is_numeric($id))
        ->map(fn ($id) => (string) $id)
        ->values();
@endphp

<select id="agentes" name="agentes_id[]" class="sr-only" multiple required
        data-initial-selected='@json($selectedIds)'>
</select>

<div id="agentes-selector"
     class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-3 gap-3"
     aria-live="polite">
    <p class="col-span-full text-sm text-gray-500 dark:text-gray-400">
        Selecciona las fechas para consultar disponibilidad.
    </p>
</div>

<p id="agentes-selection-summary" class="mt-3 text-sm text-gray-500 dark:text-gray-400">
    Ningún agente seleccionado.
</p>

@once
    <script>
        window.renderCustodiosAgentSelector = function (agents, selectedIds = null, emptyMessage = null) {
            const select = document.getElementById('agentes');
            const container = document.getElementById('agentes-selector');
            const summary = document.getElementById('agentes-selection-summary');

            if (!select || !container || !summary) return;

            const initialIds = JSON.parse(select.dataset.initialSelected || '[]').map(String);
            const currentIds = Array.from(select.selectedOptions).map(option => option.value);
            const selected = new Set((selectedIds ?? (currentIds.length ? currentIds : initialIds)).map(String));

            select.innerHTML = '';
            container.innerHTML = '';

            if (!Array.isArray(agents) || agents.length === 0) {
                const message = document.createElement('p');
                message.className = 'col-span-full text-sm text-gray-500 dark:text-gray-400';
                message.textContent = emptyMessage || 'No hay agentes para mostrar.';
                container.appendChild(message);
                summary.textContent = 'Ningún agente seleccionado.';
                return;
            }

            const refreshSummary = () => {
                const count = Array.from(select.selectedOptions).length;
                summary.textContent = count === 0
                    ? 'Ningún agente seleccionado.'
                    : `${count} agente${count === 1 ? '' : 's'} seleccionado${count === 1 ? '' : 's'}.`;
            };

            agents.forEach(agent => {
                const option = document.createElement('option');
                option.value = String(agent.id);
                option.textContent = agent.name;
                option.disabled = Boolean(agent.ocupado);
                option.selected = selected.has(option.value) && !option.disabled;
                select.appendChild(option);

                const card = document.createElement('button');
                card.type = 'button';
                card.disabled = option.disabled;
                card.setAttribute('aria-pressed', option.selected ? 'true' : 'false');

                const avatarWrap = document.createElement('span');
                avatarWrap.className = 'relative flex h-12 w-12 shrink-0 items-center justify-center overflow-hidden rounded-full bg-gradient-to-br from-amber-500 to-orange-600 text-sm font-bold text-white';

                const fallback = document.createElement('span');
                fallback.textContent = (agent.name || 'AG')
                    .split(/\s+/)
                    .filter(Boolean)
                    .slice(0, 2)
                    .map(part => part.charAt(0).toUpperCase())
                    .join('');
                avatarWrap.appendChild(fallback);

                if (agent.foto_url) {
                    const image = document.createElement('img');
                    image.src = agent.foto_url;
                    image.alt = `Foto de ${agent.name}`;
                    image.className = 'absolute inset-0 h-full w-full object-cover';
                    image.loading = 'lazy';
                    image.addEventListener('error', () => image.remove());
                    avatarWrap.appendChild(image);
                }

                const body = document.createElement('span');
                body.className = 'min-w-0 flex-1';

                const name = document.createElement('span');
                name.className = 'block truncate text-sm font-semibold text-gray-900 dark:text-white';
                name.textContent = agent.name;

                const status = document.createElement('span');
                status.className = agent.ocupado
                    ? 'mt-1 block text-xs font-medium text-red-600 dark:text-red-400'
                    : 'mt-1 block text-xs font-medium text-emerald-600 dark:text-emerald-400';
                status.textContent = agent.ocupado
                    ? `No disponible: ${agent.motivo || 'ocupado'}`
                    : 'Disponible';

                body.append(name, status);
                card.append(avatarWrap, body);

                const refreshCard = () => {
                    const isSelected = option.selected;
                    card.setAttribute('aria-pressed', isSelected ? 'true' : 'false');

                    if (option.disabled) {
                        card.className = 'flex items-center gap-3 rounded-xl border border-red-200 bg-red-50 p-3 text-left opacity-70 dark:border-red-900 dark:bg-red-950/20 cursor-not-allowed';
                    } else if (isSelected) {
                        card.className = 'flex items-center gap-3 rounded-xl border-2 border-blue-500 bg-blue-50 p-3 text-left shadow-sm transition focus:outline-none focus:ring-2 focus:ring-blue-500 dark:bg-blue-950/30';
                    } else {
                        card.className = 'flex items-center gap-3 rounded-xl border border-gray-200 bg-white p-3 text-left transition hover:border-blue-300 hover:bg-blue-50/50 focus:outline-none focus:ring-2 focus:ring-blue-500 dark:border-gray-700 dark:bg-gray-800 dark:hover:border-blue-700';
                    }
                };

                card.addEventListener('click', () => {
                    option.selected = !option.selected;
                    refreshCard();
                    refreshSummary();
                    select.dispatchEvent(new Event('change', { bubbles: true }));
                });

                refreshCard();
                container.appendChild(card);
            });

            refreshSummary();
        };
    </script>
@endonce
