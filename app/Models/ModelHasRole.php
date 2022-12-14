<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Venturecraft\Revisionable\RevisionableTrait;


class ModelHasRole extends Model
{
    use HasFactory;
    use RevisionableTrait;
    protected $primaryKey = ['role_id', 'model_id', 'model_type'];
    public $incrementing = false;
    public $timestamps = false;
    protected $fillable = [
        'role_id',
        'model_id',
        'model_type',
    ];

    protected function setKeysForSaveQuery($query)
    {
        $keys = $this->getKeyName();
        if(!is_array($keys)){
            return parent::setKeysForSaveQuery($query);
        }

        foreach($keys as $keyName){
            $query->where($keyName, '=' ,$this->getKeyForSaveQuery($keyName));
        }

        return $query;
    }

    protected function getKeyForSaveQuery($keyName = null)
    {
        if(is_null($keyName)){
            $keyName = $this->getKeyName();
        }

        if (isset($this->original[$keyName])) {
            return $this->original[$keyName];
        }

        return $this->getAttribute($keyName);
    }

    public function role(){
        return $this->belongsTo('App\Models\Role', 'role_id', 'id');
    }

    public function get_inner_value(){
        $permission = $this->permission->name;
        $role = $this->role->name;
        $model_type = backpack_user()->getMorphClass();
        return ''.$role.' ,'.$model_type.' ,'.$permission;
    }


}
