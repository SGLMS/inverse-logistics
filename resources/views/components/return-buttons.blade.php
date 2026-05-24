@props(['return'])
<flux:dropdown>
    <flux:button icon="ellipsis-vertical" color="gray" size="xs" />
    @if ($return->status->editable() && $return->quantity > 0)
        <flux:menu x-data>
            @if ($return->status === \Sglms\InverseLogistics\Enums\ReturnStatus::Pending)
                <flux:navmenu.item icon="chevron-double-right"
                    x-on:click="$dispatch('return-create-checkin', {returnId: {{ $return->id }} })">
                    {{ __('Create Check-in') }}
                </flux:navmenu.item>
                @role('super-admin|admin')
                    <flux:navmenu.item icon="trash" variant="danger"
                        x-on:click="$dispatch('return-delete', {returnId: {{ $return->id }} })">
                        {{ __('Delete') }}
                    </flux:navmenu.item>
                @endrole
            @else
                <flux:navmenu.item icon="arrow-uturn-left" 
                    x-on:click="$dispatch('return-undo-checkin', {returnId: {{ $return->id }} })">
                    {{ __('Undo Check-in') }}
                </flux:navmenu.item>
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
    @endif
</flux:dropdown>
