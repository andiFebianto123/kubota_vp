<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Vendor extends Model
{
    use \Backpack\CRUD\app\Models\Traits\CrudTrait;
    use HasFactory;
    protected $guarded = ['id'];

    protected $fillable = [
        'name','number', 'address', 'company', 'phone'
    ];

    // public function purchase()
    // {
    //     return $this->hasMany('App\Models\Church', 'country_id', 'id');
    // }
}
