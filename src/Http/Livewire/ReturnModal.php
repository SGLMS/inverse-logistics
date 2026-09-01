<?php

use Flux\Flux;
use Illuminate\Database\Eloquent\Model;
use Livewire\Attributes\On;
use Livewire\Component;
use Sglms\InverseLogistics\Enums\ReturnStatus;
use Sglms\InverseLogistics\Models\ILReturn;
use Sglms\InverseLogistics\Services\InverseLogisticsManager;

class ReturnModal extends Component
{
    public ?int $returnId = null;

    public ?ILReturn $return = null;

    public ?Model $request = null;

    public ?Model $checkout = null;

    public function mount() {}

    public function render()
    {
        return view('inverse-logistics::livewire.return-modal');
    }

    #[On('return-show')]
    public function show(int $returnId)
    {
        $this->returnId = $returnId;
        $this->return = app(InverseLogisticsManager::class)->getReturnWithProductQuantities($returnId);
        Flux::modal('return-modal')->show();
    }

    #[On('return-create-checkin')]
    public function createCheckin(int $returnId)
    {
        $this->returnId = $returnId;
        $return = ILReturn::find($returnId);
        $this->return = app(InverseLogisticsManager::class)->getReturnWithProductQuantities($returnId);
        $this->checkout = $this->return->checkout;
        if (! $this->return || ! $this->checkout) {
            $this->dispatch('notification', message: 'Return or associated checkout not found.', type: 'error');

            return;
        }
        $checkinModel = config('inverse-logistics.models.checkin');
        $checkinNumber = date('ymd')
            .str_pad($this->checkout->cf_doc_number, 8, '0', STR_PAD_LEFT);
        $checkinReference = $this->checkout->number.'-RETURN-'.$returnId;
        if ($checkinModel::where('dg_number', $checkinNumber)->where('dg_client_id', $this->return->client_id)->exists()) {
            $this->dispatch('notification', message: 'Check-in already exists for this return.', type: 'warning');

            return;
        }
        $checkin = $checkinModel::updateOrCreate([
            'dg_number' => $checkinNumber,
            'dg_reference' => $checkinReference,
        ], [
            'dg_client_id' => $this->return->client_id,
            'dg_date' => now()->toDateString(),
            'dg_arrival_time' => now(),
            'dg_driver_ssn' => $this->return->driver_id,
            'dg_driver_name' => $this->return->driver_name,
            'dg_license_plate' => $this->return->truck_number,
            'dg_observations' => 'Check-in created for return ID '.$returnId.' with products: '.json_encode($this->return->returnProductQuantities),
            'dg_type' => 'IL',
        ]);
        foreach ($this->return->returnProductQuantities as $pid => $payloadEntry) {
            app(config('inverse-logistics.services.checkin'))->addProductUnits(
                checkin: $checkin,
                productId: (int) $pid,
                units: $payloadEntry['units'] ?? 0,
                data: [
                    'batch' => $payloadEntry['batch'] ?? null,
                    'reason' => $payloadEntry['reason'] ?? null,
                ]
            );
        }
        $return->update([
            'notes' => trim(($this->return->notes ?? '')."\n".'Check-in created with ID '.$checkin->id.' and number '.$checkin->dg_number),
            'status' => ReturnStatus::Checkin->value,
        ]);
        $this->dispatch('notification', message: __('Check-in created for this return.'), type: 'success');

        Flux::modal('return-modal')->show();
    }

    #[On('return-undo-checkin')]
    public function undoCheckin(int $returnId): void
    {
        $this->returnId = $returnId;
        $this->return = app(InverseLogisticsManager::class)->getReturnWithProductQuantities($returnId);
        if (! $this->return) {
            $this->dispatch('notification', message: 'Return not found.', type: 'error');

            return;
        }

        $checkinNumber = $this->return->checkout->cf_doc_number.'00'.$returnId;
        $checkinSuffix = str_pad((string) $this->return->checkout->cf_doc_number, 8, '0', STR_PAD_LEFT);
        $checkinModel = config('inverse-logistics.models.checkin');
        $date = $this->return->route_date?->format('ymd');
        $checkin = $checkinModel::where('dg_number', 'regexp', '\\d{6}'.$checkinSuffix)
            ->where('dg_client_id', $this->return->client_id)->first();
        if (! $checkin) {
            $this->dispatch('notification', message: 'Associated check-in not found.', type: 'error');

            return;
        }
        try {
            // Use checkin service to cleanup and delete skus, pallets, boxes.
            $message = app(config('inverse-logistics.services.checkin'))->delete($checkin);
            ILReturn::find($returnId)->update([
                'notes' => trim(($this->return->notes ?? '')."\n".'Check-in with ID '.$checkin->id.' and number '.$checkin->dg_number.' deleted.'),
                'status' => ReturnStatus::Pending->value,
            ]);
            Flux::modal('return-modal')->show();
        } catch (Exception $e) {
            $this->dispatch('notification', message: 'Error deleting check-in: '.$e->getMessage(), type: 'error');

            return;
        }
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
        $this->redirect(route('inverse-logistics.index'));
    }
}
