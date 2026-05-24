<div>
    <flux:modal name="return-modal">
        @if ($return)
            <h4>{{ __('Return') }}</h4>
            <x-inverse-logistics::return-card :return="$return" />
        @endif
        @if (count($return->returnProductQuantities ?? []) > 0 && count($return->requestProductQuantities ?? []) > 0)
            <h5>{{ __('Returned Quantities') }}</h5>
            <div class="max-h-64 overflow-auto">
                <x-inverse-logistics::table-returned-quantities :return="$return" class="text-xs" />
            </div>
        @endif
    </flux:modal>
</div>
