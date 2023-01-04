<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ArticleLike extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'article_post_id',
    ];


    /*
    |--------------------------------------------------------------------------
    | RELATIONSHIPS
    |--------------------------------------------------------------------------
    */

    public function articlePost()
    {
        return $this->belongsTo(ArticlePost::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
