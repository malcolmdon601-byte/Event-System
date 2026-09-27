<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Equipment extends Model
{
    protected $fillable = ['name', 'category', 'total_quantity'];

    public function reservations()
    {
        return $this->hasMany(EquipmentReservation::class);
    }
}
