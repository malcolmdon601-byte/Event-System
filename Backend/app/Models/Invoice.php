<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Invoice extends Model
{
    protected $fillable = ['invoice_number', 'event_id', 'quotation_id', 'total', 'amount_paid', 'balance', 'status', 'due_date'];

    protected function casts(): array
    {
        return [
            'total' => 'decimal:2',
            'amount_paid' => 'decimal:2',
            'balance' => 'decimal:2',
            'due_date' => 'date',
        ];
    }

    public function event()
    {
        return $this->belongsTo(Event::class);
    }

    public function quotation()
    {
        return $this->belongsTo(Quotation::class);
    }

    public function payments()
    {
        return $this->hasMany(Payment::class);
    }
}
