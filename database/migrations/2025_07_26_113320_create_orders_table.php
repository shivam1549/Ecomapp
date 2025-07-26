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
        Schema::create('orders', function (Blueprint $table) {
            $table->id();

            // User/customer info (nullable for guest checkout if needed)
            $table->foreignId('user_id')->nullable()->constrained()->onDelete('set null');

            // Order status
            $table->string('status')->default('pending'); // pending, paid, shipped, cancelled, etc.

            // Currency & total
            $table->string('currency_code', 10)->default('USD'); // USD, INR, etc.
            $table->decimal('currency_rate', 10, 6)->default(1.000000); // exchange rate applied

            // Price-related fields
            $table->decimal('subtotal', 10, 2)->default(0);       // before discounts
            $table->decimal('discount_total', 10, 2)->default(0); // if any discount code applied
            $table->decimal('tax_total', 10, 2)->default(0);
            $table->decimal('shipping_total', 10, 2)->default(0);
            $table->decimal('grand_total', 10, 2)->default(0);    // final total

            // Billing/shipping (can be separated into another table if complex)
            $table->json('billing_address')->nullable();
            $table->json('shipping_address')->nullable();

            // Payment
            $table->string('payment_method')->nullable();      // e.g., stripe, razorpay
            $table->string('payment_status')->default('unpaid'); // unpaid, paid, failed
            $table->string('transaction_id')->nullable();      // from payment gateway

            // Metadata
            $table->uuid('order_number')->unique();            // human-readable order number
            $table->text('notes')->nullable();

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
        Schema::dropIfExists('orders');
    }
};
