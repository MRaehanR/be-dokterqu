<?php

namespace App\Models;

use App\Casts\ImageCast;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;
use Backpack\CRUD\app\Models\Traits\CrudTrait;
use Illuminate\Support\Facades\Hash;

class User extends Authenticatable implements MustVerifyEmail
{
    use CrudTrait;
    use HasRoles, HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'photo',
        'phone',
        'active',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
        'roles',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'active' => 'boolean',
        'photo' => ImageCast::class,
    ];

    /*
    |--------------------------------------------------------------------------
    | RELATIONSHIPS
    |--------------------------------------------------------------------------
    */

    public function doctorInfo()
    {
        return $this->hasOne(DoctorInfo::class, 'user_id');
    }

    public function apotekInfo()
    {
        return $this->hasOne(ApotekInfo::class, 'user_id');
    }

    public function customerInfo()
    {
        return $this->hasOne(CustomerInfo::class, 'user_id');
    }

    public function customerAddress()
    {
        return $this->hasMany(CustomerAddress::class, 'user_id');
    }


    /*
    |--------------------------------------------------------------------------
    | ACCESSORS
    |--------------------------------------------------------------------------
    */

    public function getPhotoProfileAttribute()
    {
        return (isset($this->photo)) ? env('APP_URL') . '/' . $this->photo : null;
    }

    public function getEmailVerifiedAttribute()
    {
        return isset($this->email_verified_at);
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

    public function setPasswordAttribute($value)
    {
        $this->attributes['password'] = Hash::make($value);
    }


    /*
    |--------------------------------------------------------------------------
    | SCOPES
    |--------------------------------------------------------------------------
    */

    public function scopeActive($query, $value)
    {
       return $query->where('active', $value); 
    }

    public function scopeRole($query, $name) 
    {
        return $query->with('roles')->whereHas('roles', function($query) use($name){
            $query->whereIn('name', ["$name"]);
        });
    }
}
