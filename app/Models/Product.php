<?php

namespace App\Models;

use Backpack\CRUD\app\Models\Traits\CrudTrait;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    use CrudTrait, HasFactory;

    /*
    |--------------------------------------------------------------------------
    | GLOBAL VARIABLES
    |--------------------------------------------------------------------------
    */

    protected $table = 'products';
    protected $guarded = ['id'];


    /*
    |--------------------------------------------------------------------------
    | FUNCTIONS
    |--------------------------------------------------------------------------
    */

    /*
    |--------------------------------------------------------------------------
    | RELATIONS
    |--------------------------------------------------------------------------
    */

    public function category()
    {
        return $this->belongsTo(ProductCategory::class);
    }

    public function apotekStock()
    {
        return $this->hasMany(ApotekStock::class);
    }

    public function cart()
    {
        return $this->hasMany(CartItem::class);
    }

    /*
    |--------------------------------------------------------------------------
    | SCOPES
    |--------------------------------------------------------------------------
    */

    public function scopeCategory($query, $name)
    {
        return $query->whereHas('category', function ($query) use ($name) {
            $query->whereIn('name', ["$name"]);
        });
    }

    /*
    |--------------------------------------------------------------------------
    | ACCESSORS
    |--------------------------------------------------------------------------
    */

    public function getImagesAttribute($value)
    {
        if (!$value) {
            return env('APP_URL', url('/')) . "/assets/images/default/default_image_product.webp";
        }
        return env('APP_URL', url('/')) . "/" . $value;
    }

    public function getRangePriceAttribute()
    {
        $apotekStock = ApotekStock::where('product_id', $this->id)->get();
        if (count($apotekStock) != 0) {
            $minPrice = number_format($apotekStock->min()->price, 0, null, '.');
            $maxPrice = number_format($apotekStock->max()->price, 0, null, '.');

            if ($minPrice == $maxPrice) {
                return "Rp$minPrice";
            }

            return 'Rp' . $minPrice . ' - Rp' . $maxPrice;
        }
        return $apotekStock->min();
    }


    /*
    |--------------------------------------------------------------------------
    | MUTATORS
    |--------------------------------------------------------------------------
    */
}
