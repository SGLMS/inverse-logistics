<table class="text-sm w-full">
    <thead>
        <tr class="font-bold border-b-2 *:p-2">
            <th>{{ __('Product') }}</th>
            <th>{{ __('Requested') }}</th>
            <th>{{ __('Delivered') }}</th>
            <th>{{ __('Returned') }}</th>
            <th>{{ __('Reason') }}</th>
        </tr>
    </thead>
    <tbody>
        @foreach ($return->requestProductQuantities as $pid => $quantity)
            <tr>
                <td>{{ $return->products->find($pid)?->code ?? $pid }}</td>
                <td>{{ $return->products->find($pid)?->name ?? $pid }}</td>
                <td class="text-right">{{ $quantity }}</td>
                <td class="text-right">{{ $return->returnProductQuantities[$pid]['units'] ?? '-' }}
                </td>
                <td class="text-right">
                    {{ $return->returnProductQuantities[$pid]['reason'] ?? '-' }}
                </td>
            </tr>
        @endforeach
    </tbody>
</table>
