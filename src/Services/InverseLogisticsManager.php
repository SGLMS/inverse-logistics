<?php

namespace SGLMS\InverseLogistics\Services;

class InverseLogisticsManager
{
    public function __construct(
        protected array $config = []
    ) {
    }

    public function createReturn(array $payload): array
    {
        return [
            'status' => 'created',
            'payload' => $payload,
        ];
    }

    public function approveReturn(string $returnId): bool
    {
        return true;
    }

    public function rejectReturn(string $returnId, ?string $reason = null): bool
    {
        return true;
    }
}
