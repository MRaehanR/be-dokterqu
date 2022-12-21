<?php

namespace App\Models;

use App\Casts\ImageCast;
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
        'status',
    ];
    protected $casts = [
        'ktp' => ImageCast::class,
        'npwp' => ImageCast::class,
        'surat_izin_usaha' => ImageCast::class,
        'image' => ImageCast::class,
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
    | MUTATORS
    |--------------------------------------------------------------------------
    */

    public function setNameAttribute($value)
    {
        $this->attributes['name'] = ucwords(strtolower($value));
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
