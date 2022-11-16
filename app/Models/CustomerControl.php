<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CustomerControl extends Model
{
    use HasFactory;

    protected $table = 'customer_controls';
    protected $fillable = [
        'doctor_user_id',
        'start_date',
        'next_date',
        'end_date',
    ];
}
