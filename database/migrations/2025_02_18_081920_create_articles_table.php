<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateArticlesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('articles', function (Blueprint $table) {
            $table->id(); // Tạo khóa chính tự động tăng
            $table->string('title'); // Tiêu đề bài báo
            $table->string('url'); // Liên kết bài báo
            $table->text('keywords'); // Từ khóa liên quan 
            $table->timestamps(); // Thời gian tạo và cập nhật (created_at, updated_at)
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('articles');
    }
}
