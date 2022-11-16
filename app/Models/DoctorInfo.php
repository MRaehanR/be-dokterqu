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
}
