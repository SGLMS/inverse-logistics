<x-layouts.app title="{{ __('Inverse Logistics Service') }}">
    <x-dispatcher::alert type="tip" class="">
        {{ __('Welcome to Inverse Logistics Service Package for Laravel!') }}
    </x-dispatcher::alert>
    <table class="table">
        <thead>
            <tr>
                <th>{{ __('Return ID') }}</th>
                <th>{{ __('Status') }}</th>
                <th>{{ __('Reference') }}</th>
                <th>{{ __('Notes') }}</th>
                <th>{{ __('Approved At') }}</th>
                <th>{{ __('Rejected At') }}</th>
                <th>{{ __('Route Date') }}</th>
                <th>{{ __('Driver ID') }}</th>
                <th>{{ __('Driver Name') }}</th>
                <th>{{ __('Truck') }}</th>
                <th>{{ __('Reference') }}</th>
                <th>{{ __('Requested') }}</th>
                <th>{{ __('Returned') }}</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($returns as $return)
                <tr>
                    <td>{{ $return->id }}</td>
                    <td>{{ $return->status }}</td>
                    <td>{{ $return->reference }}</td>
                    <td>{{ $return->notes }}</td>
                    <td>{{ $return->approved_at }}</td>
                    <td>{{ $return->rejected_at }}</td>
                    <td>{{ $return->route_date->format('d/m/Y') }}</td>
                    <td>{{ $return->driver_id }}</td>
                    <td>{{ $return->driver_name }}</td>
                    <td>{{ $return->truck_number }}</td>
                    {{-- <td>@dump($return->payload)</td> --}}
                    <td>
                        @if ($return->request)
                            <a href="{{ route('requests.show', $return->request->request_id) }}" class="">
                                {{ $return->request->number }}
                            </a>
                        @endif
                    </td>
                    <td class="text-right">
                        {{ Number::format(collect($return->requestProductQuantities ?? [])->sum()) }}
                    </td>
                    <td class="text-right">
                        {{ Number::format(collect($return->returnProductQuantities ?? [])->sum()) }}
                    </td>
                </tr>
                @if (count($return->returnProductQuantities ?? []) > 0)
                    <tr>
                        <td></td>
                        <td colspan="12">
                            <x-inverse-logistics::table-returned-quantities :return="$return" />
                        </td>
                    </tr>
                @endif
            @endforeach
        </tbody>
    </table>
</x-layouts.app>
