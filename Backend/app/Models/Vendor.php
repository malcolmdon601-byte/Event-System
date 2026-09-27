<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Vendor extends Model
{
    protected $fillable = ['name', 'category', 'contact_name', 'phone', 'email', 'notes', 'status'];

    public function events()
    {
        return $this->belongsToMany(Event::class, 'event_vendor')->withPivot('notes')->withTimestamps();
    }
}
