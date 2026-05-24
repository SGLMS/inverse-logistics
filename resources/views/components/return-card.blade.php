@props(['return'])
<x-dispatcher::card color="red" :code="$return->id" class="">
    <x-slot:title>
        {{ $return->checkout->number ?? ($return->request->number ?? __('N/A')) }}
        {{ $return->client->name ?? '' }}
    </x-slot:title>
    <div>
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
        |
        <flux:field class="w-32">
            {{-- <flux:label>{{ __('Delivery') }}</flux:label> --}}
            <div class="flex items-center gap-4 -mt-2">
                <flux:progress value="{{ abs($return->deliveredPercentage) ?? 0 }}" max="100" class=""
                    color="green" />
                <span class="text-sm tabular-nums ...">{{ $return->deliveredPercentage ?? 0 }}%</span>
            </div>
        </flux:field>

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
    @if (app()->environment('development') && $return->percentage)
        <div class="flex items-center gap-1">
            <flux:icon.bug-ant class="inline size-5" /> :
            <span class="">
                @dump($return->returnProductQuantities)
            </span>
        </div>
    @endif
    </div>
</x-dispatcher::card>
