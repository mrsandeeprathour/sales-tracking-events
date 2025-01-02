<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::create('variants', function (Blueprint $table) {
            $table->id();
            $table->string('variant_id')->unique();
            $table->foreignId('product_id')->constrained('products'); // Foreign key referencing the products table
            $table->string('title');
            $table->decimal('price', 8, 2); // For price, you can change the precision if needed
            $table->string('sku')->nullable();
            $table->decimal('weight', 8, 2)->nullable();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('variants');
    }

};
