<?php

namespace Sglms\InverseLogistics\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Sglms\InverseLogistics\Enums\ReturnStatus;
use Sglms\InverseLogistics\Models\Traits\ConfigureModels;

class ILReturn extends Model
{
    use ConfigureModels;

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
        return $this->belongsTo($this->modelClass('request', 'App\\Models\\Request'), 'reference', 'request_id');
    }

    public function checkout(): BelongsTo
    {
        return $this->belongsTo($this->modelClass('checkout', 'App\\Models\\Checkout'), 'reference', 'cf_request_id');
    }

    public function client(): BelongsTo
    {
        return $this->belongsTo($this->modelClass('client', 'App\\Models\\Client'), 'client_id', 'client_id');
    }

    protected function quantity(): Attribute
    {
        return Attribute::make(
            get: fn () => collect($this->payload ?? [])->sum(
                fn ($payloadEntry) => (int) data_get($payloadEntry, 'units', data_get($payloadEntry, 0, 0))
            )
        );
    }

    protected function checkinNumber(): Attribute
    {
        return Attribute::make(
            get: function () {
                $checkoutNumber = $this->checkout?->cf_doc_number ?? '0';

                return str_pad((string) $checkoutNumber, 8, '0', STR_PAD_LEFT);
            }
        );
    }

    public function checkin(): HasOne
    {
        $checkinModel = config('inverse-logistics.models.checkin');

        return $this->hasOne($checkinModel, 'dg_client_id', 'client_id')
            ->where('dg_number', 'like', '%'.$this->checkinNumber);
    }
}
