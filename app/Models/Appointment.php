<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Appointment extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'user_id',
        'doctor_id', 
        'service_id',
        'appointment_date',
        'appointment_time',
        'duration_minutes',
        'price',
        'status',
        'diagnosis', 
        'prescription', 
        'cancellation_reason',
        'cancelled_by',
        'cancelled_at'
    ];

    protected $casts = [
        'appointment_date' => 'date',
        'appointment_time' => 'datetime',
        'duration_minutes' => 'integer', 
        'cancelled_at' => 'datetime',
    ];

    // Relationships
    public function patient()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function doctor()
    {
        return $this->belongsTo(User::class, 'doctor_id');
    }

    public function service()
    {
        return $this->belongsTo(Service::class);
    }

    public function canceller()
    {
        return $this->belongsTo(User::class, 'cancelled_by');
    }
}