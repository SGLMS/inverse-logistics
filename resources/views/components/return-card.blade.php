@props(['return'])
<div class="space-y-1">
    <div class="flex items-center gap-1">
        <flux:icon.truck class="inline size-5" /> :
        <span class="">
            {{ $return->checkout->number ?? ($return->request->number ?? __('N/A')) }}
            {{ $return->client->name ?? '' }}
        </span>
    </div>
    <div class="flex items-center gap-1">
        <flux:icon.calendar class="inline size-5" /> :
        <span class="">
            {{ $return->route_date->format('d/m/Y') }}
        </span>
    </div>
    <div class="flex items-center gap-1">
        <flux:icon.boxes class="inline size-5" /> :
        <span class="whitespace-nowrap">
            {{ Number::format($return->quantity ?? 0) }}
            {{ __('Returned') }}
        </span>
        |
        <span class="text-sm whitespace-nowrap">
            {{ __('Requested') }} :
            {{ Number::format(collect($return->requestProductQuantities ?? [])->sum() ?? 0) }}
        </span>
        <flux:progress value="{{ $return->quantity ?? 0 }}"
            max="{{ collect($return->requestProductQuantities ?? [])->sum() ?? 0 }}" class="" />
    </div>
    <div class="flex items-center gap-1">
        <flux:icon.identification class="inline size-5" /> :
        <span class="">
            {{ $return->driver_name }}
            ({{ $return->driver_id }})
        </span>
        |
        <flux:icon.truck class="inline size-5" /> :
        <span class="text-sm whitespace-nowrap">
            {{ $return->truck_number }}
        </span>
    </div>
    <div class="flex items-center gap-1">
        <flux:icon.chat-bubble-bottom-center-text class="inline size-5" /> :
        <span class="">
            {{ $return->notes }}
        </span>
    </div>
</div>
