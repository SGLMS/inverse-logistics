<?php

use Livewire\Component;
use Livewire\Attributes\Computed;
use Sglms\InverseLogistics\Services\InverseLogisticsManager;

new class extends Component {
    public ?\App\Models\Request $request = null;
    public $client_id;
    public $reference;
    public $truck_number;
    public $driver_id;
    public $driver_name;
    public $product_code;
    public $quantity;
    public $notes;

    protected $rules = [
        'client_id' => 'required|exists:inv_clients,client_id',
        'reference' => 'required|integer|exists:inv_checkout_requests,request_id',
        'truck_number' => 'required|string|max:255',
        'driver_id' => 'required|string|max:255',
        'driver_name' => 'required|string|max:255',
        'product_code' => 'required|string|max:255',
        'quantity' => 'required|integer|min:1',
        'notes' => 'nullable|string',
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

    public function updatedReference()
    {
        $this->request = \App\Models\Request::where('request_id', $this->reference)->where('request_client_id', $this->client_id)->first();
    }

    public function save()
    {
        $data = $this->validate();
        $clientId = $data['client_id'];
        $this->request = \App\Models\Request::where('request_id', $data['reference'])->where('request_client_id', $clientId)->first();
        if ($this->request) {
            $date = $this->request->datetime->toDateString();
            $product = \App\Models\Product::where('product_code', $data['product_code'])->first();
            if (!$product) {
                $this->dispatch('notification', message: __('Product code :code not found.', ['code' => $data['product_code']]), type: 'error');
                return;
            }
            app(InverseLogisticsManager::class)->createReturn([
                'reference' => $this->request->request_id,
                'client_id' => $clientId,
                'notes' => $data['notes'] ?? null,
                'truck_number' => $data['truck_number'] ?? '',
                'driver_id' => $data['driver_id'] ?? '',
                'driver_name' => $data['driver_name'] ?? '',
                'date' => $date,
                'payload' => [
                    $product->product_id => [(int) $data['quantity']],
                ],
            ]);
        }

        \Flux\Flux::modal('return-add-modal')->close();
        $this->reset(['client_id', 'reference', 'truck_number', 'driver_id', 'driver_name', 'product_code', 'quantity', 'notes']);
    }
};
?>
<div>
    <h4>{{ __('Add Return') }}</h4>
    <form method="POST" class="space-y-lg" wire:submit.prevent="save">
        <fieldset class="">
            <flux:select label="{{ __('Client') }}" wire:model.live="client_id">
                <option value="">{{ __('Select Client') }}</option>
                @foreach ($this->clients as $id => $name)
                    <option value="{{ $id }}">{{ $name }}</option>
                @endforeach
            </flux:select>
        </fieldset>
        <fieldset class="grid lg:grid-cols-2 gap-lg">
            @if ($client_id)
                <flux:select label="{{ __('Reference') }} ({{ __('Document Number') }})" wire:model.live.blur="reference"
                    class="font-mono">
                    <option value="">{{ __('Select Request') }}</option>
                    @foreach ($this->requests as $r)
                        <option value="{{ $r->request_id }}" :disabled="$r->request_status_id != 2">
                            {{ $r->request_doc_number }}
                            {{ $r->datetime->format('d/m/y') }}
                        </option>
                    @endforeach
                </flux:select>
            @else
                <flux:input label="{{ __('Reference') }} ({{ __('Document Number') }})" wire:model="reference" />
            @endif
            <flux:input label="{{ __('Truck Number') }}" wire:model="truck_number" />
        </fieldset>
        <fieldset class="grid lg:grid-cols-2 gap-lg">
            <flux:input label="{{ __('Driver ID') }}" wire:model="driver_id" />
            <flux:input label="{{ __('Driver Name') }}" wire:model="driver_name" />
        </fieldset>
        <fieldset class="grid lg:grid-cols-3 gap-lg">
            <div class="lg:col-span-2">
                @if ($reference && $request)
                    <flux:select label="{{ __('Product') }}" wire:model.live.blur="product_code">
                        @foreach ($request->products as $product)
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
        </fieldset>
        <fieldset>
            <flux:textarea label="{{ __('Reason for Return') }}" wire:model="notes" />
        </fieldset>
        <div class="flex justify-end">
            <flux:button type="submit" variant="primary">
                {{ __('Submit') }}
            </flux:button>
        </div>
    </form>
</div>
