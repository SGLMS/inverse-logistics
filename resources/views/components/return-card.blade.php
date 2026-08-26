@props(['return'])
<x-dispatcher::card color="red" :code="$return->id" class="">
    <x-slot:title>
        {{ $return->checkout->number ?? ($return->request->number ?? __('N/A')) }}
        {{ $return->client->name ?? '' }}
    </x-slot:title>
    <div>
        <x-inverse-logistics::return-status :return="$return" />
        <div class="flex items-center gap-1">
            <flux:icon.truck class="inline size-5" /> :
            <span class="">
                {{ $return->checkout->number ?? ($return->request->number ?? __('N/A')) }}
                {{ $return->client->name ?? '' }}
            </span>
            <flux:link class="">
                {{ $return->checkout->number ?? ($return->request->number ?? __('N/A')) }}
                {{ $return->client->name ?? '' }}
            </flux:link>
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
            <span class="text-sm">
                {!! nl2br(e($return->notes)) !!}
            </span>
        </div>
        <div class="flex items-center gap-1">
            <flux:icon.link class="inline size-5" /> :
            {{ __('Reference') }}:
            <x-checkout.link :checkout="$return->checkout" class="m-lg" modal />
        </div>
        @if ($return->checkin)
            <div class="flex items-center gap-1">
                <flux:icon.chevron-double-right class="inline size-5" /> :
                {{ __('Checkin') }}:
                <x-checkin.link :checkin="$return->checkin" class="" modal />
            </div>
        @endif
        @if (app()->environment('development') && $return->percentage)
            <div class="flex items-center gap-1">
                <flux:icon.bug-ant class="inline size-5" /> :
                <span class="">
                    @dump($return->returnProductQuantities)
                </span>
            </div>
        @endif
    </div>

    <div class="flex justify-end">
        <x-inverse-logistics::return-buttons :return="$return" />
    </div>
</x-dispatcher::card>
