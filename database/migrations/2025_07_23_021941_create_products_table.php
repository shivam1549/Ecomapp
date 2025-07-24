<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('products', function (Blueprint $table) {
            $table->id();

            $table->string('title');
            $table->string('slug')->unique();
            $table->string('sku')->nullable(); // Optional for base product
            $table->decimal('price', 10, 2)->nullable(); // Optional for simple product

            $table->string('main_image')->nullable();
            $table->json('gallery_images')->nullable(); // Store image array as JSON

            $table->text('short_description')->nullable();
            $table->longText('long_description')->nullable();

            $table->string('meta_title')->nullable();
            $table->string('meta_description')->nullable();
            $table->string('meta_keywords')->nullable();

            $table->boolean('is_active')->default(true);
            $table->boolean('is_variable')->default(false); // true if has variations

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('products');
    }
};
