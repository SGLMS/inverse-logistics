<?php

declare(strict_types=1);

use Livewire\Component;
use Livewire\Attributes\Computed;
use Livewire\Attributes\On;


new class extends Component {
    public function mount() {}
    public function open()
    {
        Flux\Flux::modal('return-add-modal')->show();
    }

    #[Computed]
};
?>
<div>
    <flux:button.group class="flex justify-end">
        <flux:button icon="plus" variant="primary" wire:click="open">
            {{ __('Add') }}
        </flux:button>
    </flux:button.group>
    <flux:modal name="return-add-modal" title="{{ __('Add Return') }}">
        <livewire:inverse-logistics::return-add-form />
    </flux:modal>
</div>
