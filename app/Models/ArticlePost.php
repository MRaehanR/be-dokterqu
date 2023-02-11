<?php

namespace App\Models;

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
        return $this->belongsTo(User::class, 'upload_by');
    }

    public function comment()
    {
        return $this->hasMany(ArticleComment::class);
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

    public function getThumbnailAttribute($value)
    {
        if(!$value){
            return env('APP_URL', url('/'))."/assets/images/default/default_thumbnail.png";
        }
        return env('APP_URL', url('/'))."/".$value;
    }



    
    /*
    |--------------------------------------------------------------------------
    | MUTATORS
    |--------------------------------------------------------------------------
    */

    public function setThumbnailAttribute($value)
    {
        if ($value) {
            $fileName = Carbon::now()->format('YmdHis') . "_" . md5_file($value) . "." . $value->getClientOriginalExtension();
            $filePath = "storage/images/article_thumbnail/" . $fileName;
            $value->storeAs(
                "public/images/article_thumbnail",
                $fileName
            );
            $this->attributes['thumbnail'] = $filePath;
        } else {
            $this->attributes['thumbnail'] = null;
        }
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
