<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    use HasFactory;

    // Define the table if it doesn't follow Laravel's pluralization convention
    protected $table = 'products';

    // Specify the fillable attributes (fields that can be mass-assigned)
    protected $fillable = [
        'product_id',
        'title',
        'handle',
        'status',
        'tags',
        'shop_id'
    ];

    public function eventSales()
    {
        return $this->hasMany(EventSale::class);
    }
    public function variants()
    {
        return $this->hasMany(Variant::class);
    }

    public function images()
    {
        return $this->hasMany(Image::class);
    }

}
