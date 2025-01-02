<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Event extends Model
{
    use HasFactory;

    protected $fillable = [
        'event_name',
        'start_date',
        'end_date',
        'status',
        'shop_id',
    ];

    public function eventSales()
    {
        return $this->hasMany(EventSale::class);
    }
}
