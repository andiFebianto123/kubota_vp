<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Vendor extends Model
{
    use \Backpack\CRUD\app\Models\Traits\CrudTrait;
    use HasFactory;
    protected $guarded = ['id'];
    protected $table = 'vendor';

    protected $fillable = [
        'vend_name','vend_num', 'vend_addr', 'currency', 'buyer', 'vend_email', 'buyer_email'
    ];

    // public function purchase()
    // {
    //     return $this->hasMany('App\Models\Church', 'country_id', 'id');
    // }
}
