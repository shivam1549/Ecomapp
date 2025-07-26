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
        Schema::create('order_items', function (Blueprint $table) {
            $table->id();

            $table->foreignId('order_id')->constrained()->onDelete('cascade');
            $table->foreignId('product_id')->constrained()->onDelete('cascade');

            $table->foreignId('product_variation_id')->nullable(); // if it's a variable product

            $table->string('name'); // product name snapshot
            $table->decimal('price', 10, 2); // price at time of purchase (not current product price)
            $table->integer('quantity');
            $table->decimal('total', 10, 2); // price * quantity

            $table->json('attributes')->nullable(); // size, color, custom fields, etc.

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
        Schema::dropIfExists('order_items');
    }
};
