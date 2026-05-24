<?php

namespace Sglms\InverseLogistics\Services;

use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Sglms\InverseLogistics\Enums\ReturnStatus;
use Sglms\InverseLogistics\Models\ILReturn;

class InverseLogisticsManager
{
    private const REQUEST_MODEL = 'App\\Models\\Request';

    private const CHECKOUT_MODEL = 'App\\Models\\Checkout';

    public function __construct(
        protected array $config = []
    ) {}

    public function createReturn(array $data): array
    {
        $return = ILReturn::updateOrCreate(
            [
                'reference' => $data['reference'] ?? null,
                'client_id' => $data['client_id'] ?? null,
            ],
            [
                'notes' => $data['notes'] ?? null,
                'route_date' => $data['date'] ?? null,
                'driver_id' => $data['driver_id'] ?? null,
                'driver_name' => $data['driver_name'] ?? null,
                'truck_number' => $data['truck_number'] ?? null,
                'payload' => $data['payload'] ?? null,
                'status' => $data['status'] ?? ReturnStatus::Pending,
            ]
        );

        return $return->toArray();
    }

    public function approveReturn(string $returnId): bool
    {
        $return = ILReturn::findOrFail($returnId);

        return $return->forceFill([
            'status' => ReturnStatus::Approved,
            'approved_at' => Carbon::now(),
            'rejected_at' => null,
        ])->save();
    }

    public function rejectReturn(string $returnId, ?string $reason = null): bool
    {
        $return = ILReturn::findOrFail($returnId);

        return $return->forceFill([
            'status' => ReturnStatus::Rejected,
            'approved_at' => null,
            'rejected_at' => Carbon::now(),
            'notes' => $reason ?: $return->notes,
        ])->save();
    }

    public function listReturns(array $filters = []): Collection
    {
        return ILReturn::latest()->get();
    }

    private function getRequestProductQuantities(int $returnId): array
    {
        $return = ILReturn::findOrFail($returnId);
        $checkout = $return->checkout;

        if ($checkout) {
            foreach ($checkout->products->unique('product_id') as $product) {
                $quantities[$product->product_id] = $checkout->getProductDispatchedUnits($product->product_id);
            }
        }

        return $quantities ?? [];
    }

    private function getReturnProductQuantities(int $returnId): array
    {
        $return = ILReturn::findOrFail($returnId);
        foreach ($return->payload ?? [] as $productId => $payloadEntry) {
            $quantities[$productId] = [
                'units' => is_array($payloadEntry)
                ? (int) ($payloadEntry['units'] ?? $payloadEntry[0] ?? 0)
                : 0,
                'reason' => is_array($payloadEntry) ? ($payloadEntry['reason'] ?? null) : null,
            ];
        }

        return $quantities ?? [];
    }

    public function getReturnWithProductQuantities(int $returnId): ILReturn
    {
        $return = ILReturn::findOrFail($returnId);
        $return->requestProductQuantities = $this->getRequestProductQuantities($returnId);
        $return->returnProductQuantities = $this->getReturnProductQuantities($returnId);

        $return->unregisteredProducts = array_diff_key(
            $return->returnProductQuantities,
            $return->requestProductQuantities
        );

        return $return;
    }
}
