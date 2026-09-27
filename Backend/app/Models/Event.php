<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Event extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'customer_id', 'event_type_id', 'name', 'event_date', 'start_time', 'end_time',
        'venue', 'guest_count', 'budget', 'status', 'notes', 'created_by',
    ];

    protected function casts(): array
    {
        return [
            'event_date' => 'date',
            'budget' => 'decimal:2',
        ];
    }

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public function eventType()
    {
        return $this->belongsTo(EventType::class);
    }

    public function services()
    {
        return $this->belongsToMany(Service::class, 'event_service')
            ->withPivot(['quantity', 'unit_price', 'subtotal'])
            ->withTimestamps();
    }

    public function quotations()
    {
        return $this->hasMany(Quotation::class);
    }

    public function invoices()
    {
        return $this->hasMany(Invoice::class);
    }

    public function payments()
    {
        return $this->hasMany(Payment::class);
    }

    public function staff()
    {
        return $this->belongsToMany(Staff::class, 'event_staff')->withTimestamps();
    }

    public function vendors()
    {
        return $this->belongsToMany(Vendor::class, 'event_vendor')->withPivot('notes')->withTimestamps();
    }

    public function equipmentReservations()
    {
        return $this->hasMany(EquipmentReservation::class);
    }

    public function tasks()
    {
        return $this->hasMany(Task::class);
    }

    public function messages()
    {
        return $this->hasMany(Message::class);
    }
}
