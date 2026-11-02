<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('orders', function (Blueprint $table) {
            $table->id();

            // relationships
            $table->foreignId('user_id')->constrained()->onDelete('cascade'); // PS who placed order
            $table->foreignId('product_id')->constrained()->onDelete('cascade'); // product being bought
            $table->foreignId('personnel_id')->nullable()->constrained('users')->onDelete('set null'); // NSP assigned

            // copied data snapshot(deduplication to prevent previous order product totals from updating if product prices change in the future)
            $table->string('product_name');
            $table->integer('product_price'); // In pesewas/cents
            $table->integer('amount')->default(1); // Quantity
            $table->integer('total_price'); // amount * product_price

            // notes and statuses
            $table->text('order_note')->nullable();
            $table->string('status')->default('pending');

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};
