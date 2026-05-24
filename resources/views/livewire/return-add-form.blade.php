<?php

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
    public $product_code;
    public $quantity;
    public $batch;
    public $notes;

    protected $rules = [
        'client_id' => 'required|exists:inv_clients,client_id',
        'reference' => 'required|integer|exists:inv_checkout_forms,cf_request_id',
        'truck_number' => 'required|string|max:255',
        'driver_id' => 'required|string|max:255',
        'driver_name' => 'required|string|max:255',
        'product_code' => 'required|string|max:255',
        'quantity' => 'required|integer|min:1',
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

    public function updatedReference()
    {
        $this->checkout = \App\Models\Checkout::where('cf_request_id', $this->reference)->where('cf_client_id', $this->client_id)->first();

        if (!$this->checkout) {
            $this->product_code = null;

            return;
        }

        $this->product_code = $this->checkout->products->first()?->product_code;
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
            $product = \App\Models\Product::where('product_code', $data['product_code'])->first();
            if (!$product) {
                $this->dispatch('notification', message: __('Product code :code not found.', ['code' => $data['product_code']]), type: 'error');
                return;
            }
            $existingReturn = ILReturn::query()->where('reference', $this->checkout->cf_request_id)->where('client_id', $clientId)->first();

            $existingPayload = (array) ($existingReturn?->payload ?? []);
            $productId = (string) $product->product_id;
            $existingProductPayload = (array) ($existingPayload[$productId] ?? []);

            $payload = $existingPayload;
            $payload[$productId] = [...$existingProductPayload, 'units' => (int) $data['quantity'], 'batch' => $data['batch'] ?? null, 'reason' => $data['notes'] ?? null];

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
                        <flux:select.option value="{{ $c->cf_request_id }}">
                            {{ $c->cf_doc_number }}
                            {{ $c->datetime->format('d/m/y') }}
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
            <fieldset class="grid lg:grid-cols-4 gap-lg">
                <div class="lg:col-span-2">
                    @if ($reference && $checkout)
                        <flux:select label="{{ __('Product') }}" wire:model.live.blur="product_code">
                            @foreach ($checkout->products->unique('product_code') as $product)
                                <flux:select.option value="{{ $product->product_code }}">
                                    {{ $product->product_code }} - {{ $product->product_name }}
                                </flux:select.option>
                            @endforeach
                        </flux:select>
                    @else
                        <flux:input label="{{ __('Product Code') }}" wire:model="product_code"
                            placeholder="PRDCTCD-XXXX" />
                    @endif
                </div>
                <flux:input label="{{ __('Quantity') }}" type="number" wire:model="quantity" class="" />
                <flux:input label="{{ __('Batch') }}" wire:model="batch" />
            </fieldset>
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
