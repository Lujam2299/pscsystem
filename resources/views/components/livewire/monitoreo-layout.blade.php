{{-- resources/views/components/livewire/monitoreo-layout.blade.php --}}
<div class="min-h-screen mx-auto bg-blue-50 dark:bg-blue-950 max-w-7xl">
  <div class="container p-6 px-4 py-6 mx-auto bg-white rounded-lg shadow-md dark:bg-gray-800">
    <x-breadcrumb :items="$breadcrumbItems" />
    <div class="mb-6">
      <h1 class="flex items-center gap-2 text-2xl font-bold text-gray-800 dark:text-gray-100">
        {{ $titleMain }}
      </h1>
      @if(!empty($helpText))
      <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">{{ $helpText }}</p>
      @endif
    </div>
    <div>
      {{ $slot }}
    </div>
  </div>
</div>
