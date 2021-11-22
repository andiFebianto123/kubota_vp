<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserOtp extends Model
{
    use HasFactory;

    public function setCreatedAtAttribute($value)
    {
        $this->attributes['created_at'] = date("Y/m/d H:i:s", strtotime($value));
    }
    public function setUpdatedAtAttribute($value)
    {
        $this->attributes['updated_at'] = date("Y/m/d H:i:s", strtotime($value));
    }
}
