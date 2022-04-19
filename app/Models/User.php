<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Hash;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;
use Venturecraft\Revisionable\RevisionableTrait;

class User extends Authenticatable
{
    use \Backpack\CRUD\app\Models\Traits\CrudTrait;
    use HasApiTokens, HasFactory, Notifiable, HasRoles;
    use RevisionableTrait;

    protected $fillable = [
        'name',
        'username',
        'email',
        'vendor_id',
        'role_id', 
        'password'
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    public function vendor()
    {
        return $this->belongsTo('App\Models\Vendor', 'vendor_id', 'id');
    }


    public function showRole(){
        $roleName = $this->getRoleNames();
        if(count($roleName) > 0){
            return '<span>'.$roleName[0].'</span>';
        }
        return '-';
    }

    public function setEmailAttribute($value)
    {
        $this->attributes['email'] = str_replace(" ", "", $value);
    }
    
    public function setCreatedAtAttribute($value)
    {
        $this->attributes['created_at'] = date("Y/m/d H:i:s", strtotime($value));
    }


    public function setUpdatedAtAttribute($value)
    {
        $this->attributes['updated_at'] = date("Y/m/d H:i:s", strtotime($value));
    }

    public function excelExportAdvance(){
        $url = url('admin/user-export');
        return '<a class="btn btn-sm btn-primary-vp" href="'.$url.'"><i class="la la-file-excel"></i> Export </a>';
    }
}
