@props([
    'pack' => null,
    'name' => null
])

@php
$iconPack = \Blinq\Icons\IconPack::get($pack);
$icon = null;
$variant = explode("/", $pack)[1] ?? null;
$missing = false;

if ($iconPack) {
    $icon = $iconPack->getIcon($name, $variant, $attributes);
}

if (!$icon) {
    $missing = true;
    $attributes = $attributes->merge([
        'title' => 'Icon not found: ' . $name,
    ]);

    $iconPack = \Blinq\Icons\IconPack::get('fallback');
    $icon = $iconPack->getIcon('missing', null, $attributes);
}

@endphp

@if(!$missing)
<!-- icon: {{ $pack }} {{ $name }} -->
@else
<!-- icon not found: {{ $pack }} {{ $name }} -->
@endif
{!! $icon !!}