<?php

namespace App\Models;

use App\Casts\ImageCast;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class ApotekInfo extends Model
{
    use HasFactory;

    protected $table = 'apotek_info';
    protected $fillable = [
        'user_id',
        'province_id',
        'city_id',
        'name',
        'address',
        'ktp',
        'npwp',
        'surat_izin_usaha',
        'image',
        'latitude',
        'longitude',
        'status',
    ];
    protected $casts = [
        'ktp' => ImageCast::class,
        'npwp' => ImageCast::class,
        'surat_izin_usaha' => ImageCast::class,
    ];
    protected $appends = [
        'province_name',
        'city_name',
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

    public function getImageAttribute($values)
    {
        if($values){
            $files = [];
            foreach (json_decode($values) as $value) {
                array_push($files, env('APP_URL', url('/'))."/".$value);
            }
            return $files;
        }
        return array(env('APP_URL', url('/'))."/assets/images/default/default_image_apotek.jpg");
    }

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

    
    /*
    |--------------------------------------------------------------------------
    | MUTATORS
    |--------------------------------------------------------------------------
    */

    public function setNameAttribute($value)
    {
        $this->attributes['name'] = ucwords(strtolower($value));
    }

    public function setImageAttribute($value)
    {
        if ($value) {
            $filePath = [];
            foreach ($value as $file) {
                $fileName = Carbon::now()->format('YmdHis') . "_" . md5_file($file) . "." . $file->getClientOriginalExtension();
                array_push($filePath, "storage/images/apotek_image/$fileName");
                $file->storeAs(
                    "public/images/apotek_image",
                    $fileName
                );
            }
            $this->attributes['image'] = json_encode($filePath);
        } else {
            $this->attributes['image'] = null;
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
}
