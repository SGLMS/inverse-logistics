<div>
    <flux:modal name="return-show-modal">
        @if ($return)
            {{ $return->id }}
            {{ $return->quantity }}
            @dump($return->returnProductQuantities)
        @endif
    </flux:modal>
</div>
