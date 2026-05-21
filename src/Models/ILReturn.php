<?php

namespace Sglms\InverseLogistics\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ILReturn extends Model
{
    private const REQUEST_MODEL = 'App\\Models\\Request';

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
        'truck_number'
    ];

    protected $casts = [
        'payload' => 'array',
        'approved_at' => 'datetime',
        'rejected_at' => 'datetime',
        'route_date' => 'date',
    ];

    public function request(): BelongsTo
    {
        return $this->belongsTo(self::REQUEST_MODEL, 'reference', 'request_id');
    }
}