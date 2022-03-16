<?php

namespace App\Models;

use Backpack\CRUD\app\Models\Traits\CrudTrait;
use Illuminate\Database\Eloquent\Model;
use Venturecraft\Revisionable\RevisionableTrait;

class DeliverySerial extends Model
{
    use CrudTrait;
    use RevisionableTrait;

    protected $table = 'delivery_serial';
    protected $guarded = ['id'];

    public function userCreate()
    {
        return $this->belongsTo('App\Models\User', 'created_by', 'id');
    }

    public function userUpdate()
    {
        return $this->belongsTo('App\Models\User', 'updated_by', 'id');
    }

    public function delivery()
    {
        return $this->belongsTo('App\Models\Delivery', 'ds_num', 'ds_num');

    }

}
