<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EquipmentReservation extends Model
{
    protected $fillable = ['equipment_id', 'event_id', 'quantity', 'start_date', 'end_date'];

    protected function casts(): array
    {
        return ['start_date' => 'date', 'end_date' => 'date'];
    }

    public function equipment()
    {
        return $this->belongsTo(Equipment::class);
    }

    public function event()
    {
        return $this->belongsTo(Event::class);
    }
}
