@props(['return'])
<flux:button.group x-data>
    {{-- <flux:button icon="eye" color="gray" size="xs"
        x-on:click="$dispatch('return-show', { returnId: {{ $return->id }} })" /> --}}
    <flux:button icon="eye" color="gray" size="xs"
        :href="route('inverse-logistics.show',['id' => $return->id])" />
    @if ($return->status->editable() && $return->quantity > 0)
        <flux:dropdown>
            <flux:button icon="ellipsis-vertical" color="gray" size="xs" />
            <flux:menu>
                @if ($return->status == \Sglms\InverseLogistics\Enums\ReturnStatus::Pending)
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
                    @if ($return->status == \Sglms\InverseLogistics\Enums\ReturnStatus::Checkin)
                        <flux:navmenu.item icon="arrow-uturn-left"
                            x-on:click="confirm('{{ __('Are you sure?') }}') && $dispatch('return-undo-checkin', {returnId: {{ $return->id }} })">
                            {{ __('Undo Check-in') }}
                        </flux:navmenu.item>
                    @endif
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
    @endif
</flux:button.group>
