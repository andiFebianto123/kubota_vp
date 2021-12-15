<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;


class Role extends Model
{
    use \Backpack\CRUD\app\Models\Traits\CrudTrait;
    use HasFactory;

    protected $fillable = [
        'name',
        'guard_name',
    ];

    function permission(){
        $id = $this->id;
        return "<a href='javascript:void(0)'  data-toggle='modal' data-target='#modalListPermission' class='btn btn-sm btn-link' id='permission' data-permission='{$id}'><i class='la la-lock'></i> Permission</a>";
    }
}
