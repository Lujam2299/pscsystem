<x-app-layout>
    <x-navbar></x-navbar>
    <div class="py-4 px-2 sm:py-6 sm:px-4">
        <div class="container mx-auto max-w-7xl">
                @if(session('success'))
                    <div class="bg-green-500 text-white p-2 mb-4 rounded-lg">{{ session('success') }}</div>
                @endif
                @livewire('admigestionusuarios')
        </div>
    </div>
</x-app-layout>

