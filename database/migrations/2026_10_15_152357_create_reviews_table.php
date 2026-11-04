<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
        public function up(): void
    {
        Schema::create('reviews', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('product_id')->constrained()->cascadeOnDelete();
            $table->unsignedTinyInteger('rating'); // 1 đến 5 sao
            $table->text('comment');
            $table->json('images')->nullable(); // Mảng đường dẫn ảnh góc chụp thật
            $table->boolean('is_verified_purchase')->default(true); // Nhãn "Đã mua hàng tại GearHub"
            $table->timestamps();

            // Mỗi user đánh giá 1 lần cho 1 sản phẩm (hoặc có thể bỏ unique nếu cho phép đánh giá mỗi lần mua)
            $table->unique(['user_id', 'product_id']);
        });
    }

public function down(): void
{
    Schema::dropIfExists('reviews');
}
};
