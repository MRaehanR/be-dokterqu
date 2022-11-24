<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ApotekInfo extends Model
{
    use HasFactory;

    protected $table = 'apotek_info';
    protected $fillable = [
        'user_id',
        'name',
        'address',
        'ktp',
        'npwp',
        'surat_izin_usaha',
        'image',
        'latitude',
        'longitude',
    ];
}
