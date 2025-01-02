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
    Schema::create('images', function (Blueprint $table) {
        $table->id();
        $table->unsignedBigInteger('image_id');
        $table->unsignedBigInteger('variant_id');
        $table->unsignedBigInteger('product_id');
        $table->string('src'); // Image source URL
        $table->string('alt')->nullable(); // Optional alt text for the image
        $table->timestamps();
    });
}

public function down()
{
    Schema::dropIfExists('images');
}
};
