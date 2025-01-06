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
    Schema::create('products', function (Blueprint $table) {
        $table->id();
        $table->unsignedBigInteger('shop_id');
        $table->string('product_id');
        $table->string('title');
        $table->string('handle')->nullable();
        $table->string('status')->nullable();; // You can modify status options as needed
        $table->text('tags')->nullable();
        $table->timestamps();
    });
}

public function down()
{
    Schema::dropIfExists('products');
}
};
