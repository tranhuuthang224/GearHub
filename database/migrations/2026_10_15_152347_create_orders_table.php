<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
{
    Schema::create('orders', function (Blueprint $table) {
        $table->id();
        $table->string('code')->unique();
        $table->foreignId('user_id')->constrained()->restrictOnDelete();
        $table->foreignId('coupon_id')->nullable()
              ->constrained()->nullOnDelete();
        $table->string('receiver_name');
        $table->string('phone', 20);
        $table->string('shipping_address');
        $table->enum('payment_method', ['cod'])->default('cod');
        $table->enum('status', ['pending', 'shipping', 'completed', 'cancelled'])
              ->default('pending');
        $table->decimal('subtotal', 12, 0);
        $table->decimal('discount', 12, 0)->default(0);
        $table->decimal('total', 12, 0);
        $table->timestamps();

        $table->index('status');
        $table->index('created_at'); // phục vụ biểu đồ doanh thu 7 ngày
    });
}

public function down(): void
{
    Schema::dropIfExists('orders');
}
};
