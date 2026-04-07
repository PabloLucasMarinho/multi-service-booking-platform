<?php

namespace App\Models;

use App\Enums\PaymentMethod;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AppointmentPayment extends Model
{
    use HasUuids;

    protected $primaryKey = 'uuid';
    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'appointment_uuid',
        'amount',
        'payment_method',
    ];

    protected $casts = [
        'amount'         => 'decimal:2',
        'payment_method' => PaymentMethod::class,
    ];

    public function appointment(): BelongsTo
    {
        return $this->belongsTo(Appointment::class, 'appointment_uuid', 'uuid');
    }

    public function getRouteKeyName(): string
    {
        return 'uuid';
    }
}
