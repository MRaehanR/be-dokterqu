<?php

namespace App\Models;

use App\Casts\ImageCast;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

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
    ];
    protected $casts = [
        'cv' => ImageCast::class,
        'str' => ImageCast::class,
        'ktp' => ImageCast::class,
    ];
    protected $appends = [
        'type_doctor',
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
        return $this->belongsTo(DoctorType::class, 'id');
    }


    /*
    |--------------------------------------------------------------------------
    | ACCESSORS
    |--------------------------------------------------------------------------
    */

    public function getTypeDoctorAttribute()
    {
        $type = DoctorType::where('id', $this->type_doctor_id)->first()->name;
        return ucfirst($type);
    }

    
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
}
