<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Venturecraft\Revisionable\RevisionableTrait;

class Configuration extends Model
{
    use \Backpack\CRUD\app\Models\Traits\CrudTrait;
    use HasFactory;
    use RevisionableTrait;
    
    protected $guarded = ['id'];
    protected $fillable = [
        'label','name', 'value', 'is_integer'
    ];
}
