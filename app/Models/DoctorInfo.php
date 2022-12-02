<?php

namespace App\Models;

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
}
