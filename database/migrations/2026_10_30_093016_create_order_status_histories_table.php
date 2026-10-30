<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
{
    Schema::create('order_status_histories', function (Blueprint $table) {
        $table->id();
        $table->foreignId('order_id')->constrained()->cascadeOnDelete();
        $table->string('from_status', 20)->nullable();      // trống: lần ghi đầu tiên (tạo đơn)
        $table->string('to_status', 20);
        $table->foreignId('changed_by')->nullable()->constrained('users')->nullOnDelete();
        $table->timestamp('created_at')->useCurrent();

        $table->index(['order_id', 'id']);
    });
}

public function down(): void
{
    Schema::dropIfExists('order_status_histories');
}
};
