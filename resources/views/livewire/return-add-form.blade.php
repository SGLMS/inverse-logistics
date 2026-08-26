<?php

declare(strict_types=1);

use Livewire\Component;
use Livewire\Attributes\Computed;
use Sglms\InverseLogistics\Models\ILReturn;
use Sglms\InverseLogistics\Services\InverseLogisticsManager;

new class extends Component {
    public ?\App\Models\Checkout $checkout = null;

    public $client_id;
    public $reference;
    public $truck_number;
    public $driver_id;
    public $driver_name;
    public $batch;
    public $notes;
    public array $returns = [];

    protected $rules = [
        'client_id' => 'required|exists:inv_clients,client_id',
        'reference' => 'required|integer|exists:inv_checkout_forms,cf_request_id',
        'truck_number' => 'required|string|max:255',
        'driver_id' => 'required|string|max:255',
        'driver_name' => 'required|string|max:255',
        'returns.*' => 'nullable|integer|min:0',
        'notes' => 'nullable|string',
        'batch' => 'nullable|string|max:255',
    ];

    public function mount() {}

    #[Computed]
    public function clients()
    {
        return \App\Models\Client::orderBy('client_name')->pluck('client_name', 'client_id');
    }

    #[Computed]
    public function requests()
    {
        return \App\Models\Request::where('request_client_id', $this->client_id)
            ->whereBetween('request_arrival_time', [now()->subDays(30), now()])
            ->orderBy('request_arrival_time', 'desc')
            ->get();
    }

    #[Computed]
    public function checkouts()
    {
        return \App\Models\Checkout::where('cf_client_id', $this->client_id)
            ->whereBetween('cf_arrival_time', [now()->subDays(30), now()])
            ->orderBy('cf_arrival_time', 'desc')
            ->get();
    }

    #[Computed]
    public function existingReferences()
    {
        return ILReturn::where('client_id', $this->client_id)->pluck('reference');
    }

    public function updatedReference()
    {
        $this->checkout = \App\Models\Checkout::where('cf_request_id', $this->reference)->where('cf_client_id', $this->client_id)->first();
        $this->returns = [];

        if (!$this->checkout) {
            return;
        }

        foreach ($this->checkout->products->unique('product_code') as $product) {
            $this->returns[$product->product_code] = null;
        }
        $this->driver_name = $this->checkout->cf_driver_name;
        $this->driver_id = $this->checkout->cf_driver_ssn;
        $this->truck_number = $this->checkout->cf_license_plate;
    }

    public function save()
    {
        $data = $this->validate();
        $clientId = $data['client_id'];
        $this->checkout = \App\Models\Checkout::where('cf_request_id', $data['reference'])->where('cf_client_id', $clientId)->first();
        if ($this->checkout) {
            $date = $this->checkout->datetime->toDateString();
            $existingReturn = ILReturn::query()->where('reference', $this->checkout->cf_request_id)->where('client_id', $clientId)->first();
            $payload = (array) ($existingReturn?->payload ?? []);

            foreach ((array) ($data['returns'] ?? []) as $productCode => $qty) {
                if (!$qty) {
                    continue;
                }
                $product = \App\Models\Product::where('product_code', $productCode)->first();
                if (!$product) {
                    $this->dispatch('notification', message: __('Product code :code not found.', ['code' => $productCode]), type: 'error');
                    continue;
                }
                $productId = (string) $product->product_id;
                $existingProductPayload = (array) ($payload[$productId] ?? []);
                $payload[$productId] = [...$existingProductPayload, 'units' => (int) $qty, 'batch' => $data['batch'] ?? null, 'reason' => $data['notes'] ?? null];
            }

            app(InverseLogisticsManager::class)->createReturn([
                'reference' => $this->checkout->cf_request_id,
                'client_id' => $clientId,
                'notes' => $data['notes'] ?? null,
                'truck_number' => $data['truck_number'] ?? '',
                'driver_id' => $data['driver_id'] ?? '',
                'driver_name' => $data['driver_name'] ?? '',
                'date' => $date,
                'payload' => $payload,
            ]);
        }

        \Flux\Flux::modal('return-add-modal')->close();
        $this->resetExcept(['client_id']);
    }

    public function verified()
    {
        $data = $this->validate([
            'client_id' => 'required|exists:inv_clients,client_id',
            'reference' => 'required|integer|exists:inv_checkout_forms,cf_request_id',
        ]);
        $clientId = $data['client_id'];
        $this->checkout = \App\Models\Checkout::where('cf_request_id', $data['reference'])->where('cf_client_id', $clientId)->first();
        if (!$this->checkout) {
            $this->dispatch('notification', message: __('Request not found.'), type: 'error');
            return;
        }
        $date = $this->checkout->datetime->toDateString();

        $return = app(InverseLogisticsManager::class)->createReturn([
            'reference' => $this->checkout->cf_request_id,
            'client_id' => $clientId,
            'notes' => __('Delivered in full.'),
            'truck_number' => $this->checkout->cf_license_plate,
            'driver_id' => $this->checkout->cf_driver_ssn,
            'driver_name' => $this->checkout->cf_driver_name,
            'date' => $date,
            'status' => \Sglms\InverseLogistics\Enums\ReturnStatus::Verified,
            'payload' => [],
        ]);

        \Flux\Flux::modal('return-add-modal')->close();
        $this->resetExcept(['client_id']);
    }
};
?>
<div>
    <h4>{{ __('Delivery Confirmation') }} / {{ __('Return') }}</h4>
    <form method="POST" class="space-y-lg" wire:submit.prevent="save">
        <fieldset class="">
            <flux:select label="{{ __('Client') }}" wire:model.live="client_id">
                <option value="">{{ __('-- Select --') }}</option>
                @foreach ($this->clients as $id => $name)
                    <option value="{{ $id }}">{{ $name }}</option>
                @endforeach
            </flux:select>
        </fieldset>
        <fieldset class="gap-lg">
            @if ($client_id)
                <flux:select label="{{ __('Reference') }} ({{ __('Document Number') }})" wire:model.live="reference"
                    class="font-mono">
                    <flux:select.option value="">{{ __('-- Select --') }}</flux:select.option>
                    @foreach ($this->checkouts as $c)
                        <flux:select.option value="{{ $c->cf_request_id }}"
                            :disabled="$this->existingReferences()->contains($c->cf_request_id)">
                            {{ $c->documentTypeLabel }}
                            #{{ $c->cf_doc_number }}
                            [{{ $c->datetime->format('d/m/Y') }}]
                        </flux:select.option>
                    @endforeach
                </flux:select>
            @else
                <flux:input label="{{ __('Reference') }} ({{ __('Document Number') }})" wire:model="reference" />
            @endif
        </fieldset>
        @if ($checkout)
            <fieldset class="grid lg:grid-cols-3 gap-lg">
                <flux:input label="{{ __('Truck\'s License Plate') }}" wire:model="truck_number" />
                <flux:input label="{{ __('Driver ID') }}" wire:model="driver_id" />
                <flux:input label="{{ __('Driver Name') }}" wire:model="driver_name" />
            </fieldset>
            @foreach ($checkout->products->unique('product_code') as $product)
                <fieldset class="grid lg:grid-cols-4 gap-lg">
                    <div class="lg:col-span-2">
                        <flux:input :label="$loop->first ? __('Product') : null"
                            value="{{ $product->product_code }} - {{ $product->product_name }}"
                            :disabled="true" />
                    </div>
                    <flux:input :label="$loop->first ? __('Dispatched') : null" type="number"
                        value="{{ $checkout->getProductDispatchedUnits($product->product_id) }}" class=""
                        input:class="text-right" :disabled="true" />
                    <flux:input :label="$loop->first ? __('Return') : null" type="number"
                        wire:model="returns.{{ $product->product_code }}" class="" input:class="text-right"
                        placeholder="##" max="{{ $checkout->getProductDispatchedUnits($product->product_id) }}" />
                </fieldset>
            @endforeach
            <fieldset>
                <flux:textarea label="{{ __('Reason') }}" wire:model="notes" />
            </fieldset>
        @endif
        <div class="flex justify-end">
            <flux:button.group>
                <flux:button type="button" variant="primary" icon="shield-check" wire:click="verified"
                    :disabled="!$checkout">
                    {{ __('Confirmed') }} ({{ __('No Return') }})
                </flux:button>
                <flux:button type="submit" variant="danger" icon="hard-drive" :disabled="!$checkout">
                    {{ __('Add Return') }}
                </flux:button>
            </flux:button.group>
        </div>
    </form>
</div>
