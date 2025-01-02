<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EventSale extends Model
{
    use HasFactory;

    // Specify the columns that are mass-assignable
    protected $fillable = [
        'total_inventory',
        'sold_inventory',
        'inhand_inventory',
        'variant_id',
        'product_id',
        'event_id'
    ];

    // Define relationships
    public function variant()
    {
        return $this->belongsTo(Variant::class);
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function event()
    {
        return $this->belongsTo(Event::class);
    }
}
