<?php

namespace App\Models;

use App\Casts\ImageCast;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ArticlePost extends Model
{
    use \Backpack\CRUD\app\Models\Traits\CrudTrait;
    use HasFactory;

    protected $table = 'article_posts';
    protected $fillable = [
        'upload_by',
        'category_id',
        'title',
        'body',
        'slug',
        'thumbnail',
        'status',
    ];
    protected $casts = [
        'thumbnail' => ImageCast::class,
    ];


    /*
    |--------------------------------------------------------------------------
    | RELATIONSHIPS
    |--------------------------------------------------------------------------
    */

    public function category()
    {
        return $this->belongsTo(ArticleCategory::class);
    }

    public function author()
    {
        return $this->belongsTo(User::class, 'id');
    }

    public function comment()
    {
        return $this->hasMany(ArticleComment::class, 'article_post_id');
    }

    public function like()
    {
        return $this->hasMany(ArticleLike::class, 'article_post_id');
    }


    /*
    |--------------------------------------------------------------------------
    | ACCESSORS
    |--------------------------------------------------------------------------
    */

    public function getAuthorAttribute()
    {
        $name = User::where('id', $this->upload_by)->first()->name;
        return ucfirst($name);
    }

    public function getCategoryNameAttribute()
    {
        $category = ArticleCategory::where('id', $this->category_id)->first()->name;
        return $category;
    }

    
    /*
    |--------------------------------------------------------------------------
    | MUTATORS
    |--------------------------------------------------------------------------
    */

    public function setThumbnailAttribute($value)
    {
        $fileName = Carbon::now()->format('YmdHis') . "_" . md5_file($value) . "." . $value->getClientOriginalExtension();
        $filePath = "storage/images/article_thumbnail/" . $fileName;
        $value->storeAs(
            "public/images/article_thumbnail",
            $fileName
        );
        $this->attributes['thumbnail'] = $filePath;
    }


    /*
    |--------------------------------------------------------------------------
    | SCOPES
    |--------------------------------------------------------------------------
    */

    public function scopeStatus($query, $value)
    {
        return $query->where('status', $value);
    }

    public function scopeCategory($query, $name) 
    {
        return $query->with('category')->whereHas('category', function($query) use($name){
            $query->whereIn('name', ["$name"]);
        });
    }
}
