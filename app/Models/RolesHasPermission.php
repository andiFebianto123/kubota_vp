<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Venturecraft\Revisionable\Revisionable;
use Venturecraft\Revisionable\RevisionableTrait;
use Illuminate\Database\Eloquent\Builder;


class RolesHasPermission extends Model
{
    use \Backpack\CRUD\app\Models\Traits\CrudTrait;
    use HasFactory;
    use RevisionableTrait;

    protected $revisionEnabled = true;

    protected $table = "role_has_permissions";

    protected $guarded = ['permission_id', 'role_id'];

    protected $primaryKey = ['permission_id', 'role_id'];

    public $incrementing = false;

    public $timestamps = false;

    protected $fillable = [
        'permission_id',
        'role_id',
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

    /**
     * Get the primary key value for a save query.
     *
     * @param mixed $keyName
     * @return mixed
     */
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

    public function permission(){
        return $this->belongsTo('App\Models\Permission', 'permission_id', 'id');
    }

    public function role(){
        return $this->belongsTo('App\Models\Role', 'role_id', 'id');
    }

    public function get_inner_value(){
        $permission = $this->permission->name;
        $role = $this->role->name;
        return ''.$permission.' ,'.$role;
    }


    


}
