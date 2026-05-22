@props(['return'])
<flux:dropdown>
    <flux:button icon="ellipsis-vertical" color="gray" size="xs" />
    <flux:menu x-data>
        @if ($return->quantity > 0)
            <flux:navmenu.item icon="chevron-double-right"
                x-on:click="$dispatch('return-create-checkin', {returnId: {{ $return->id }} })">
                {{ __('Create Checkin') }}
            </flux:navmenu.item>
            @role('super-admin|admin')
                <flux:navmenu.item icon="trash" variant="danger"
                    x-on:click="$dispatch('return-delete', {returnId: {{ $return->id }} })">
                    {{ __('Delete') }}
                </flux:navmenu.item>
            @endrole
        @endif
        @if ($return->can_be_approved)
            <flux:navmenu.item wire:click="$emit('approveReturn', {{ $return->id }})">
                {{ __('Approve') }}
            </flux:navmenu.item>
        @endif
        @if ($return->can_be_rejected)
            <flux:navmenu.item wire:click="$emit('rejectReturn', {{ $return->id }})">
                {{ __('Reject') }}
            </flux:navmenu.item>
        @endif
    </flux:menu>
</flux:dropdown>
