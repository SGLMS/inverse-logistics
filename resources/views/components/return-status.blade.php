@props(['return'])
<flux:badge :color="$return->status?->color() ?? 'gray'">
    {{ $return->status?->label() ?? $return->status }}
</flux:badge>