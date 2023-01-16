<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

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
}
