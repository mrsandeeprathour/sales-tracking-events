<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Variant extends Model
{
    use HasFactory;

    // Define the table if it doesn't follow Laravel's pluralization convention
    protected $table = 'variants';

    // Specify the fillable attributes (fields that can be mass-assigned)
    protected $fillable = [
        'variant_id',
        'product_id',
        'title',
        'price',
        'sku',
        'weight',
    ];


    // Define the relationship with the Product model
    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function images()
    {
        return $this->hasMany(Image::class);
    }

    public function eventSales()
    {
        return $this->hasMany(EventSale::class);
    }
}
