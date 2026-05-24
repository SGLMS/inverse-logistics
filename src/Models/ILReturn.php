<?php

namespace Sglms\InverseLogistics\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Sglms\InverseLogistics\Enums\ReturnStatus;

class ILReturn extends Model
{
    private const REQUEST_MODEL = 'App\\Models\\Request';

    private const CHECKOUT_MODEL = 'App\\Models\\Checkout';

    private const CLIENT_MODEL = 'App\\Models\\Client';

    protected $table = 'inverse_logistics_returns';

    protected $fillable = [
        'reference',
        'client_id',
        'status',
        'payload',
        'notes',
        'approved_at',
        'rejected_at',
        'route_date',
        'driver_id',
        'driver_name',
        'truck_number',
    ];

    protected $casts = [
        'status' => ReturnStatus::class,
        'payload' => 'array',
        'approved_at' => 'datetime',
        'rejected_at' => 'datetime',
        'route_date' => 'date',
    ];

    public function request(): BelongsTo
    {
        return $this->belongsTo(self::REQUEST_MODEL, 'reference', 'request_id');
    }

    public function checkout(): BelongsTo
    {
        return $this->belongsTo(self::CHECKOUT_MODEL, 'reference', 'cf_request_id');
    }

    public function client(): BelongsTo
    {
        return $this->belongsTo(self::CLIENT_MODEL, 'client_id', 'client_id');
    }

    protected function quantity(): Attribute
    {
        return Attribute::make(
            get: fn () => collect($this->payload ?? [])->sum(
                fn ($payloadEntry) => (int) data_get($payloadEntry, 'units', data_get($payloadEntry, 0, 0))
            )
        );
    }
}
