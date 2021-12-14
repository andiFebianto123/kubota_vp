<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Hash;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;


class User extends Authenticatable
{
    use \Backpack\CRUD\app\Models\Traits\CrudTrait;
    use HasApiTokens, HasFactory, Notifiable, HasRoles;

    /**
     * The attributes that are mass assignable.
     *
     * @var string[]
     */
    protected $fillable = [
        'name',
        'username',
        'email',
        'vendor_id',
        'role_id', 
        'password'
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    public function vendor()
    {
        return $this->belongsTo('App\Models\Vendor', 'vendor_id', 'id');
    }

    // public function role()
    // {
    //     return $this->belongsTo('App\Models\Role', 'role_id', 'id');
    // }

    public function showRole(){
        $roleName = $this->getRoleNames();
        if(count($roleName) > 0){
            return '<span>'.$roleName[0].'</span>';
        }
        return '-';
    }

    // public function setPasswordAttribute($value) {
    //     $this->attributes['password'] = Hash::make($value);
    // }

    public function setCreatedAtAttribute($value)
    {
        $this->attributes['created_at'] = date("Y/m/d H:i:s", strtotime($value));
    }
    public function setUpdatedAtAttribute($value)
    {
        $this->attributes['updated_at'] = date("Y/m/d H:i:s", strtotime($value));
    }
}
