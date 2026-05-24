<x-layouts.app title="{{ __('Inverse Logistics Service') }}">
    <x-dispatcher::alert type="tip" class="">
        {{ __('Welcome to Inverse Logistics Service Package for Laravel!') }}
    </x-dispatcher::alert>

    <livewire:inverse-logistics::return-add-modal />
    <livewire:inverse-logistics::return-show />

    <table class="table w-full">
        <thead>
            <tr>
                <th>{{ __('ID') }}</th>
                <th>{{ __('Status') }}</th>
                <th>{{ __('Reference') }}</th>
                <th>{{ __('Client') }}</th>
                <th>{{ __('Date') }}</th>
                <th>{{ __('Truck') }}</th>
                <th>{{ __('Driver') }}</th>
                <th>{{ __('Requested') }}</th>
                <th>{{ __('Returned') }}</th>
                <th>{{ __('Notes') }}</th>
                {{-- <th>{{ __('Approved At') }}</th>
                <th>{{ __('Rejected At') }}</th> --}}
            </tr>
        </thead>
        <tbody>
            @foreach ($returns as $return)
                @php
                    $requestQuantity = collect($return->requestProductQuantities ?? [])->sum();
                @endphp

                <tr>
                    <td>
                        <x-inverse-logistics::return-buttons :return="$return" />
                    </td>
                    <td>
                        <x-inverse-logistics::return-status :return="$return" />
                    </td>
                    <td>
                        <x-inverse-logistics::return-reference-link :return="$return" :debug="! app()->environment('production')" />
                    </td>
                    <td>{{ $return->client->name ?? '' }}</td>
                    <td>{{ $return->route_date->format('d/m/Y') }}</td>
                    <td>{{ $return->truck_number }}</td>
                    <td>
                        {{ $return->driver_name }}
                        <span class="block text-sm text-gray-500 italic">{{ $return->driver_id }}</span>
                    </td>
                    <td class="text-right">
                        {{ Number::format($requestQuantity) }}
                    </td>
                    <td class="text-right whitespace-nowrap">
                        <div class="flex items-center justify-end gap-1">
                            <span class="text-xl font-bold">
                                {{ Number::format($return->quantity) }}
                            </span>
                        </div>
                    </td>
                    <td>{{ $return->notes }}</td>
                    {{-- <td>{{ $return->approved_at }}</td>
                    <td>{{ $return->rejected_at }}</td> --}}
                </tr>
                @if (count($return->returnProductQuantities ?? []) > 0 && count($return->requestProductQuantities ?? []) > 0)
                    <tr>
                        <td></td>
                        <td colspan="12" class="bg-white dark:bg-zinc-900">
                            <x-inverse-logistics::table-returned-quantities :return="$return" />
                        </td>
                    </tr>
                @endif
                @if (count($return->unregisteredProducts ?? []) > 0)
                    <tr>
                        <td></td>
                        <td colspan="12" class="bg-white dark:bg-zinc-900">
                            <x-dispatcher::alert type="error">
                                {{-- @dump($return->unregisteredProducts) --}}
                                <p>{{ __('The following product IDs were marked as returned, but no record was found in the system.') }}
                                </p>
                                <p class="font-bold text-mono">
                                    {{ collect($return->unregisteredProducts)->keys()->join(', ') }}
                                </p>
                            </x-dispatcher::alert>
                        </td>
                    </tr>
                @endif
            @endforeach
        </tbody>
    </table>
</x-layouts.app>
