<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Image extends Model
{
    use HasFactory;

    // Define the table if it doesn't follow Laravel's pluralization convention
    protected $table = 'images';

    // Specify the fillable attributes (fields that can be mass-assigned)
    protected $fillable = [
        'variant_id',
        'product_id',
        'src',
        'image_id',
        'alt',
    ];

    // Define the relationship with the Variant model
    public function variant()
    {
        return $this->belongsTo(Variant::class, 'variant_id'); // The foreign key is 'variant_id'
    }

    // Define the relationship with the Product model
    public function product()
    {
        return $this->belongsTo(Product::class, 'product_id'); // The foreign key is 'product_id'
    }
}
