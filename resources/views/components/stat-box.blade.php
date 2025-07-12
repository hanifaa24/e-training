@props([
    'title', 
    'value', 
    'color' => 'text-gray-800', 
    'icon' => 'shield-check', 
    'iconBg' => 'bg-gray-300'
])

<div class="flex items-center p-4 bg-white rounded-lg shadow">
    {{-- Icon box kiri --}}
    <div class="w-12 h-12 flex items-center justify-center rounded-md {{ $iconBg }} text-white mr-4">
        <x-heroicon-o-{{ $icon }} class="w-6 h-6" />
    </div>

    {{-- Konten utama --}}
    <div>
        <div class="text-sm text-gray-500">{{ $title }}</div>
        <div class="text-xl font-bold {{ $color }}">{{ $value }}</div>
    </div>
</div>
