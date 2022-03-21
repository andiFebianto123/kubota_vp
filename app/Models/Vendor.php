<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Venturecraft\Revisionable\RevisionableTrait;

class Vendor extends Model
{
    use \Backpack\CRUD\app\Models\Traits\CrudTrait;
    use HasFactory;
    use RevisionableTrait;
    
    protected $guarded = ['id'];
    protected $table = 'vendor';

    protected $fillable = [
        'vend_name','vend_num', 'vend_addr', 'currency', 'buyer', 'vend_email', 'buyer_email'
    ];

    public function setVendEmailAttribute($value)
    {
        $this->attributes['vend_email'] = str_replace(" ", "", $value);
    }

    public function setBuyerEmailAttribute($value)
    {
        $this->attributes['buyer_email'] = str_replace(" ", "", $value);
    }
}
