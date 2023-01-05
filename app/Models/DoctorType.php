<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DoctorType extends Model
{
    use HasFactory;

    protected $table = 'doctor_type';
    protected $fillable = [
        'name',
    ];


    /*
    |--------------------------------------------------------------------------
    | RELATIONSHIPS
    |--------------------------------------------------------------------------
    */

    public function doctorInfo()
    {
        return $this->hasMany(DoctorInfo::class, 'type_doctor_id');
    }
}
