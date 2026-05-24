<div>
    <flux:modal name="return-show-modal">
        @if ($return)
            <h4>{{ __('Return') }}</h4>
            <x-inverse-logistics::return-card :return="$return" />
            {{ $return->id }}
            {{ $return->quantity }}
            @dump($return->returnProductQuantities)
        @endif
    </flux:modal>
</div>
