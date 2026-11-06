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
        // 1. Bảng bài viết khoe góc máy (Community Posts)
        Schema::create('community_posts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('title');
            $table->string('slug')->unique();
            $table->text('description')->nullable();
            $table->string('image');
            $table->string('style')->default('minimalist')->index(); // minimalist, cyberpunk_rgb, white_aesthetic, wood_classic
            $table->unsignedInteger('likes_count')->default(0);
            $table->unsignedInteger('comments_count')->default(0);
            $table->unsignedInteger('views_count')->default(0);
            $table->boolean('is_featured')->default(false)->index();
            $table->timestamps();
        });

        // 2. Bảng trung gian gắn thẻ GearHub có trong góc máy (Tagged Products)
        Schema::create('community_post_product', function (Blueprint $table) {
            $table->id();
            $table->foreignId('community_post_id')->constrained()->cascadeOnDelete();
            $table->foreignId('product_id')->constrained()->cascadeOnDelete();
            $table->timestamps();

            $table->unique(['community_post_id', 'product_id']);
        });

        // 3. Bảng lượt thích bài viết (Likes)
        Schema::create('community_post_likes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('community_post_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->timestamps();

            $table->unique(['community_post_id', 'user_id']);
        });

        // 4. Bảng bình luận bài viết (Comments)
        Schema::create('community_post_comments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('community_post_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->text('content');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('community_post_comments');
        Schema::dropIfExists('community_post_likes');
        Schema::dropIfExists('community_post_product');
        Schema::dropIfExists('community_posts');
    }
};
