@props(['return', 'debug' => false])
@if ($return->checkout)
    <flux:link href="{{ route('checkouts.show', $return->checkout->cf_id) }}" class="cursor-pointer text-purple-700 font-mono font-semibold" target="_blank">
        {{ $return->checkout->number }}
    </flux:link>
    @if ($debug)
        <span class="text-xs font-mono">
            ( {{ $return->reference }} {{ $return->client->name ?? '' }} )
        </span>
    @endif
@else
    {{ $return->reference }}
@endif
