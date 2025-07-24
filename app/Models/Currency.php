<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Currency extends Model
{
     protected $fillable = ['code', 'symbol', 'exchange_rate', 'is_default'];

    // Optional: Accessor for formatting
    public function getFormattedRateAttribute()
    {
        return "{$this->symbol} 1 = {$this->exchange_rate} base";
    }
}
