<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Task extends Model
{
    protected $fillable = ['event_id', 'title', 'description', 'assigned_staff_id', 'due_date', 'priority', 'status'];

    protected function casts(): array
    {
        return ['due_date' => 'date'];
    }

    public function event()
    {
        return $this->belongsTo(Event::class);
    }

    public function assignedStaff()
    {
        return $this->belongsTo(Staff::class, 'assigned_staff_id');
    }
}
