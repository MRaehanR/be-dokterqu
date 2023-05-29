<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;
use Backpack\CRUD\app\Models\Traits\CrudTrait;
use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Hash;

class User extends Authenticatable implements MustVerifyEmail
{
    use CrudTrait;
    use HasRoles, HasApiTokens, HasFactory, Notifiable;

    public const TYPE_DOCTOR = 'doctor';
    public const TYPE_APOTEK_OWNER = 'apotek_owner';
    public const TYPE_CUSTOMER = 'customer';

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
        'gender',
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
    ];

    protected $appends = [
        'is_online',
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

    public function customerAddress()
    {
        return $this->hasMany(CustomerAddress::class, 'user_id');
    }

    public function articlePost()
    {
        return $this->hasMany(ArticlePost::class, 'upload_by');
    }

    public function articleComment()
    {
        return $this->hasMany(ArticleComment::class);
    }

    public function articleLike()
    {
        return $this->hasMany(ArticleLike::class);
    }

    public function productCart()
    {
        return $this->hasMany(CartItem::class);
    }

    public function operationalTimes()
    {
        return $this->hasMany(OperationalTime::class);
    }


    /*
    |--------------------------------------------------------------------------
    | ACCESSORS
    |--------------------------------------------------------------------------
    */

    public function getEmailVerifiedAttribute()
    {
        return isset($this->email_verified_at);
    }

    public function getPhotoAttribute($value)
    {
        if(!$value){
            if($this->roles->first()->name == 'doctor' && $this->gender == 'male') return env('APP_URL', url('/'))."/assets/images/default/default_photo_profile_doctor_male.webp";
            if($this->roles->first()->name == 'doctor' && $this->gender == 'female') return env('APP_URL', url('/'))."/assets/images/default/default_photo_profile_doctor_female.jpeg";
            
            return env('APP_URL', url('/'))."/assets/images/default/default_photo_profile_customer.png";
        }
        return env('APP_URL', url('/'))."/".$value;
    }

    public function getGenderAttribute($value)
    {
        if($value === 'm'){
            return 'male';
        } else if($value === 'f'){
            return 'female';
        }
    }

    public function getIsOnlineAttribute()
    {
        return Cache::has('is_online_' . $this->id);
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

    public function setPhotoAttribute($value)
    {
        if ($value) {
            $fileName = Carbon::now()->format('YmdHis') . "_" . md5_file($value) . "." . $value->getClientOriginalExtension();
            $filePath = "storage/images/photo_profile/" . $fileName;
            $value->storeAs(
                "public/images/photo_profile",
                $fileName
            );
            $this->attributes['photo'] = $filePath;
        } else {
            $this->attributes['photo'] = null;
        }
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
