@props(['return', 'debug' => false])
@if ($return->request)
    <flux:link href="{{ route('requests.show', $return->request->request_id) }}" class="cursor-pointer" target="_blank">
        {{ $return->request->number }}
    </flux:link>
    @if ($debug)
        <span class="text-xs font-mono">
            ( {{ $return->reference }} {{ $return->client->name ?? '' }} )
        </span>
    @endif
@else
    {{ $return->reference }}
@endif
