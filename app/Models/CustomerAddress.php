<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class CustomerAddress extends Model
{
    use HasFactory;

    protected $table = 'customer_addresses';
    protected $fillable = [
        'user_id',
        'label',
        'address',
        'note',
        'recipient',
        'phone',
        'latitude',
        'longitude',
        'default',
        'province_id',
        'city_id',
    ];
    protected $casts = [
        'default' => 'boolean',
    ];


    /*
    |--------------------------------------------------------------------------
    | RELATIONSHIP
    |--------------------------------------------------------------------------
    */

    public function user()
    {
        return $this->belongsTo(User::class);
    }


    /*
    |--------------------------------------------------------------------------
    | ACCESSORS
    |--------------------------------------------------------------------------
    */

    public function getProvinceNameAttribute()
    {
        $province = DB::table('provinces')->where('prov_id', $this->province_id)->first();
        return $province->prov_name;
    }

    public function getCityNameAttribute()
    {
        $province = DB::table('cities')->where('city_id', $this->city_id)->first();
        return $province->city_name;
    }
}
