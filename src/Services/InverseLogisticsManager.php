<?php

namespace Sglms\InverseLogistics\Services;

class InverseLogisticsManager
{
    private const REQUEST_MODEL = 'App\\Models\\Request';

    public function __construct(
        protected array $config = []
    ) {}

    public function createReturn(array $data): array
    {
        $return = \Sglms\InverseLogistics\Models\ILReturn::updateOrCreate(
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
                'status' => 'pending',
            ]
        );
        return $return->toArray();
    }

    public function approveReturn(string $returnId): bool
    {
        return true;
    }

    public function rejectReturn(string $returnId, ?string $reason = null): bool
    {
        return true;
    }

    public function listReturns(array $filters = []): \Illuminate\Support\Collection
    {
        return \Sglms\InverseLogistics\Models\ILReturn::latest()->get();
    }

    public function getRequestProductQuantities(string $returnId): array
    {
        $return = \Sglms\InverseLogistics\Models\ILReturn::findOrFail($returnId);
        $requestModel = self::REQUEST_MODEL;
        $request = $requestModel::where('request_id', $return->reference)->first();

        if ($request) {
            foreach ($request->products as $product) {
                $quantities[$product->product_id] = $request->getProductRequestedUnits($product->product_id);
            }
        }

        return $quantities ?? [];
    }

    public function getReturnProductQuantities(string $returnId): array
    {
        $return = \Sglms\InverseLogistics\Models\ILReturn::findOrFail($returnId);
        foreach($return->payload ?? [] as $pid => $info) {
            $quantities[$pid] = is_array($info) ? $info[0] : 0;
        }
        return $quantities ?? [];
    }
}
