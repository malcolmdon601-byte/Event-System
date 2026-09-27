<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Staff extends Model
{
    use HasFactory;

    protected $fillable = ['user_id', 'name', 'role_title', 'phone', 'email', 'status'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function events()
    {
        return $this->belongsToMany(Event::class, 'event_staff')->withTimestamps();
    }

    public function tasks()
    {
        return $this->hasMany(Task::class, 'assigned_staff_id');
    }
}
