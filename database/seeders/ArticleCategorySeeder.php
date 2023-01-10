<?php

namespace Database\Seeders;

use App\Models\ArticleCategory;
use Illuminate\Database\Seeder;

class ArticleCategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        ArticleCategory::create([
            'name' => 'mata',
            'slug' => 'mata',
        ]);
        ArticleCategory::create([
            'name' => 'kulit dan kelamin',
            'slug' => 'kulit-dan-kelamin',
        ]);
    }
}
