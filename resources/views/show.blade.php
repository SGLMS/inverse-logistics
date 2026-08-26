<x-layouts.app title="{{ __('Inverse Logistics Service') }}">
    @php
        $attributes = array_merge($return->toArray(), [
            'quantity' => $return->quantity,
            'checkin_number' => $return->checkin_number,
        ]);
    @endphp

    <section class="space-y-lg m-lg">
        <x-inverse-logistics::return-card :return="$return" />
        <x-inverse-logistics::table-returned-quantities :return="$return" class="table"/>
    </section>


    <div class="space-y-lg">
        @foreach ($attributes as $attribute => $value)
            <div class="flex gap-lg">
                <span class="font-semibold w-48">{{ $attribute }}</span>
                <span>{{ is_scalar($value) ? $value : json_encode($value) }}</span>
            </div>
        @endforeach
    </div>

</x-layouts.app>
