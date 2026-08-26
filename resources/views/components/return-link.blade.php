@props(['return', 'modal' => false])

@php
    $att = $attributes->merge(['class' => 'font-mono text-sm cursor-pointer hover:underline']);
@endphp

@if ($modal)
    <flux:link x-data x-on:click="$dispatch('return-show', { returnId: {{ $return->id }} })"
        {{ $attributes->merge(['class' => 'font-mono text-sm cursor-pointer hover:underline']) }}>
        {{ $return->checkout->number ?? ($return->request->number ?? __('N/A')) }}
    </flux:link>
@else
    <flux:link :href="route('inverse-logistics.show',['id' => $return->id])"
        {{ $attributes->merge(['class' => 'font-mono text-sm cursor-pointer hover:underline']) }}>
        {{ $return->checkout->number ?? ($return->request->number ?? __('N/A')) }}
    </flux:link>
@endif
