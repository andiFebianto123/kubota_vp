<?php

namespace App\Http\Controllers\Admin;

use App\Http\Requests\RoleRequest;
use Backpack\CRUD\app\Http\Controllers\CrudController;
use Backpack\CRUD\app\Library\CrudPanel\CrudPanelFacade as CRUD;
use App\Models\Role;
use Spatie\Permission\Models\Role as RoleSpatie;
use Spatie\Permission\Models\Permission;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Helpers\Constant;
use App\Models\ModelHasRole;
use App\Models\RolesHasPermission;
use Exception;
use Prologue\Alerts\Facades\Alert;


class RoleCrudController extends CrudController
{
    use \Backpack\CRUD\app\Http\Controllers\Operations\ListOperation;
    use \Backpack\CRUD\app\Http\Controllers\Operations\CreateOperation;
    use \Backpack\CRUD\app\Http\Controllers\Operations\UpdateOperation;
    use \Backpack\CRUD\app\Http\Controllers\Operations\DeleteOperation;
  
    public function setup()
    {
        CRUD::setModel(\App\Models\Role::class);
        CRUD::setRoute(config('backpack.base.route_prefix') . '/role');
        CRUD::setEntityNameStrings('role', 'roles');

        if(Constant::checkPermission('Read Role')){
            $this->crud->allowAccess('list');
        }else{
            $this->crud->denyAccess('list');
        }

        $this->data['option_role'] = Role::get()->pluck('name', 'id');
        $this->crud->setListView('backpack::crud.role', $this);
    }


    protected function setupListOperation()
    {

        CRUD::column('name');
        $this->crud->addButtonFromView('top', 'update_role', 'update_role', 'end');
        $this->crud->addButtonFromModelFunction('line', 'permission', 'permission', 'end');

        if(!Constant::checkPermission('Update Role')){
            $this->crud->removeButton('update');
            $this->crud->removeButton('update_role');
        }
        if(!Constant::checkPermission('Create Role')){
            $this->crud->removeButton('create');
        }
        if(!Constant::checkPermission('Delete Role')){
            $this->crud->removeButton('delete');
        }
    }


    protected function setupCreateOperation()
    {
        if(!Constant::checkPermission('Create Role')){
            $this->crud->denyAccess('create');
        }

        CRUD::setValidation(RoleRequest::class);
        CRUD::field('name');
        CRUD::addField([
            'name' => 'guard_name',
            'type' => 'hidden',
            'value' => 'web'
        ]);
    }

    
    protected function setupUpdateOperation()
    {
        if(!Constant::checkPermission('Update Role')){
            $this->crud->denyAccess('update');
        }
        $this->setupCreateOperation();
    }


    public function getPermissionOfRole(){
        $role = RoleSpatie::find(request()->input('role'));
        if($role == null){
            return response()->json([
                'status' => false,
                'alert' => 'danger',
                'message' => 'Role tidak ditemukan',
                'result' => []
            ], 400);
        }
        $getRolePermission = $role->permissions->mapWithKeys(function($item, $index){
            return [$index => $item->name];
        })->all();
        $getAllPermission = Permission::all();
        $permissionWithResultCheck = [];
        if($getAllPermission->values()->count() > 0){
            foreach($getAllPermission as $permission){
                if(in_array($permission->name, $getRolePermission)){
                    // jika salah satu role permission ada di permission
                    array_push($permissionWithResultCheck, [
                        'id' => $permission->id,
                        'name' => $permission->name,
                        'description' => $permission->description,
                        'slug' => true
                    ]);
                }else{
                    array_push($permissionWithResultCheck, [
                        'id' => $permission->id,
                        'name' => $permission->name,
                        'description' => $permission->description,
                        'slug' => false
                    ]);
                }
            }
        }else{
            // jika data permission kosong
            return response()->json([
                'status' => false,
                'alert' => 'warning',
                'message' => 'Data permission untuk saat ini masih kosong',
                'result' => []
            ], 400);
        }
        return response()->json([
            'status' => true,
            'alert' => 'success',
            'message' => 'success',
            'result' => $permissionWithResultCheck
        ], 200);
    }


    public function changeRolePermission(Request $request){
        if($request->input('role') != null){
            // dapatkan id role
            $role = $request->input('role');
            $nameRole = Role::where('id', $role)->first();
                // dapatkan id permission
            DB::beginTransaction();
            try {
                // DB::table('role_has_permissions')->where('role_id', $role)->delete();
                $rolePermissions = RolesHasPermission::where('role_id', $role)->get();
                if($rolePermissions->count() > 0){
                    foreach($rolePermissions as $rolePermission){
                        $rolePermission->delete();
                    }
                }
                if($request->input('permission') != null){
                    $insertData = collect($request->input('permission'))->map(function($value) use($role){
                        return ['permission_id' => $value, 'role_id' => $role];
                    });
                    foreach($insertData as $insert){
                        $insertRolePermission = new RolesHasPermission;
                        $insertRolePermission->permission_id = $insert['permission_id'];
                        $insertRolePermission->role_id = $insert['role_id'];
                        $insertRolePermission->save();
                    }
                    // DB::table('role_has_permissions')->insert($insertData->values()->all()); 
                }
                DB::commit();
                return response()->json([
                    'status' => true,
                    'alert' => 'success',
                    'message' => 'Berhasil lakukan perubahan permission pada role '.$nameRole->name
                ], 200);
            }catch(\Exception $e){
                DB::rollback();
                return response()->json([
                    'status' => false,
                    'alert' => 'danger',
                    'message' => $e->getMessage()
                ], 400);
            }
        }else{
            // jika role kosong
            return response()->json([
                'status' => false,
                'alert' => 'warning',
                'message' => 'Role tidak tersedia'
            ], 200);
        }
    }


    function showPermission(Request $req){
        if($req->input('role') != null){
            DB::beginTransaction();
            try{
                $permissionToRole = RoleSpatie::where('id', $req->input('role'))->first();
                // dd($permissionToRole->permissions->toArray());
                DB::commit();
                return response()->json([
                    'status' => true,
                    'role' => $permissionToRole->name,
                    'result' => $permissionToRole->permissions,
                ], 200);
            }catch(\Exception $e){
                DB::rollback();
                return response()->json([
                    'status' => false,
                    'alert' => 'danger',
                    'message' => $e->getMessage()
                ], 400);
            }
            
        }else{
            return response()->json([
                'status' => false,
                'alert' => 'warning',
                'message' => 'Role tidak tersedia'
            ], 400);
        }
    }


    public function destroy($id)
    {
        $this->crud->hasAccessOrFail('delete');

        $id = $this->crud->getCurrentEntryId() ?? $id;

        DB::beginTransaction();
        try{
            $allowDelete = true;
            if(ModelHasRole::where('role_id', $id)->exists()){
                $allowDelete = false;
            }
            if ($allowDelete) {
                $response = $this->crud->delete($id);
                DB::commit();
                return $response;
            }else{
                DB::rollback();
                $customMessage['danger'][0] = 'This role is still in use';
                
                return $customMessage;  
            }
            
        }
        catch(Exception $e){
            DB::rollback();
            throw $e;
        }
    }
}
