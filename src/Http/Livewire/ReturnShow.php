<?php

use App\Models\Checkin;
use App\Models\Request;
use App\Services\CheckinService;
use Flux\Flux;
use Livewire\Attributes\On;
use Livewire\Component;
use Sglms\InverseLogistics\Models\ILReturn;
use Sglms\InverseLogistics\Services\InverseLogisticsManager;

class ReturnShow extends Component
{
    public ?int $returnId = null;

    public ?ILReturn $return = null;

    public ?Request $request = null;

    public function mount() {}

    public function render()
    {
        return view('inverse-logistics::livewire.return-show');
    }

    #[On('return-show')]
    public function show(int $returnId)
    {
        Flux::modal('return-show-modal')->show();
    }

    #[On('return-create-checkin')]
    public function createCheckin(int $returnId)
    {
        $this->returnId = $returnId;
        $this->return = app(InverseLogisticsManager::class)->getReturnWithProductQuantities($returnId);
        $this->request = $this->return->request;
        if(! $this->return || ! $this->request) {
            $this->dispatch('notification', message: 'Return or associated request not found.', type: 'error');

            return;
        }
        $checkinNumber = $this->request->request_id.'000'.$returnId;
        $checkinReference = $this->request->number.'-RETURN-'.$returnId;
        if (Checkin::where('dg_number', $checkinNumber)->where('dg_client_id', $this->return->client_id)->exists()) {
            $this->dispatch('notification', message: 'Check-in already exists for this return.', type: 'warning');

            return;
        }
        $checkin = Checkin::updateOrCreate([
            'dg_number' => $checkinNumber,
            'dg_reference' => $checkinReference,
        ], [
            'dg_client_id' => $this->return->client_id,
            'dg_date' => now()->toDateString(),
            'dg_arrival_time' => now(),
            'dg_driver_ssn' => $this->return->driver_id,
            'dg_driver_name' => $this->return->driver_name,
            'dg_license_plate' => $this->return->truck_number,
            'dg_reference' => __('Return').'-'.$this->request->number,
            'dg_observations' => 'Check-in created for return ID '.$returnId.' with products: '.json_encode($this->return->returnProductQuantities),
        ]);
        foreach ($this->return->returnProductQuantities as $pid => $quantity) {
            app(CheckinService::class)->addProductUnits(
                checkin: $checkin,
                productId: (int) $pid,
                units: $quantity,
                data: ['batch' => '555']
            );
        }
        $this->dispatch('notification', message: __('Check-in created for this return.'), type: 'success');

        Flux::modal('return-show-modal')->show();
    }

    #[On('return-delete')]
    public function delete(int $returnId)
    {
        $this->returnId = $returnId;
        $this->return = app(InverseLogisticsManager::class)->getReturnWithProductQuantities($returnId);
        if (! $this->return) {
            $this->dispatch('notification', message: 'Return not found.', type: 'error');

            return;
        }
        $this->return->delete();
        $this->dispatch('notification', message: __('Return deleted.'), type: 'success');
    }
}
