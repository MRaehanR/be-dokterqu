<?php

namespace App\Models;

use App\Casts\ImageCast;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class DoctorInfo extends Model
{
    use HasFactory;

    protected $table = 'doctor_info';
    protected $fillable = [
        'user_id',
        'type_doctor_id',
        'experience',
        'alumnus',
        'alumnus_tahun',
        'tempat_praktik',
        'cv',
        'str',
        'ktp',
        'status',
        'price_homecare',
        'is_available',
        'address',
        'latitude',
        'longitude',
    ];
    protected $casts = [
        'cv' => ImageCast::class,
        'str' => ImageCast::class,
        'ktp' => ImageCast::class,
        'is_available' => 'boolean',
    ];


    /*
    |--------------------------------------------------------------------------
    | RELATIONSHIPS
    |--------------------------------------------------------------------------
    */

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function doctorType()
    {
        return $this->belongsTo(DoctorType::class, 'type_doctor_id');
    }


    /*
    |--------------------------------------------------------------------------
    | ACCESSORS
    |--------------------------------------------------------------------------
    */





    /*
    |--------------------------------------------------------------------------
    | MUTATORS
    |--------------------------------------------------------------------------
    */

    public function setAlumnusAttribute($value)
    {
        $this->attributes['alumnus'] = ucwords(strtolower($value));
    }

    public function setTempatPraktikAttribute($value)
    {
        $this->attributes['tempat_praktik'] = ucwords(strtolower($value));
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

    public function scopeDoctorType($query, $name)
    {
        return $query->whereHas('doctorType', function ($query) use ($name) {
            $query->where('name', 'like', "%$name%");
        });
    }
}
