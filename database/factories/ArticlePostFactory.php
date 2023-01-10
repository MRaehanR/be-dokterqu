<?php

namespace Database\Factories;

use App\Models\ArticleCategory;
use App\Models\ArticleComment;
use App\Models\ArticlePost;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Factories\Sequence;
use Illuminate\Support\Facades\Storage;

class ArticlePostFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        $status = [
            'draft',
            'published',
        ];
        $categoryCount = count(ArticleCategory::all());

        return [
            'upload_by' => 1,
            'category_id' => rand(1, $categoryCount),
            'title' => $this->faker->sentence(),
            'body' => '<p>'.$this->faker->paragraphs(5, '<br/>\n').'</p>',
            'slug' => $this->faker->slug(),
            'status' => $status[rand(0,1)],
        ];
    }

    public function addComments()
    {
        return $this->afterCreating(function() {
            $userCount = count(User::all());
            $articleCount = count(ArticlePost::all());

            ArticleComment::create([
                'user_id' => rand(1, $userCount),
                'article_post_id' => rand(1, $articleCount),
                'body' => $this->faker->sentence(),
            ]);
        });
    }

    public function addChildComments()
    {
        return $this->afterCreating(function() {
            $userCount = count(User::all());
            $articleCount = count(ArticlePost::all());
            $articleCommentCount = count(ArticleComment::all());
            ArticleComment::create([
                'user_id' => rand(1, $userCount),
                'parent_id' => rand(1, $articleCommentCount),
                'article_post_id' => rand(1, $articleCount),
                'body' => $this->faker->sentence(),
            ]);
        });
    }
}
