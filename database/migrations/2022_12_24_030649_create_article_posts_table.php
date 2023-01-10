<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateArticlePostsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('article_posts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('upload_by')->constrained('users')->cascadeOnUpdate();
            $table->foreignId('category_id')->constrained('article_categories')->cascadeOnUpdate();
            $table->enum('status', ['draft', 'published'])->default('draft');
            $table->string('thumbnail')->nullable();
            $table->string('title');
            $table->text('body');
            $table->string('slug');
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
        Schema::dropIfExists('article_posts');
    }
}
