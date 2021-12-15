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


/**
 * Class RoleCrudController
 * @package App\Http\Controllers\Admin
 * @property-read \Backpack\CRUD\app\Library\CrudPanel\CrudPanel $crud
 */
class RoleCrudController extends CrudController
{
    use \Backpack\CRUD\app\Http\Controllers\Operations\ListOperation;
    use \Backpack\CRUD\app\Http\Controllers\Operations\CreateOperation;
    use \Backpack\CRUD\app\Http\Controllers\Operations\UpdateOperation;
    use \Backpack\CRUD\app\Http\Controllers\Operations\DeleteOperation;
    // use \Backpack\CRUD\app\Http\Controllers\Operations\ShowOperation;

    /**
     * Configure the CrudPanel object. Apply settings to all operations. 
     * 
     * @return void
     */
    public function setup()
    {
        CRUD::setModel(\App\Models\Role::class);
        CRUD::setRoute(config('backpack.base.route_prefix') . '/role');
        CRUD::setEntityNameStrings('role', 'roles');

        $this->data['option_role'] = Role::get()->pluck('name', 'id');
        $this->crud->setListView('backpack::crud.role', $this);
    }

    /**
     * Define what happens when the List operation is loaded.
     * 
     * @see  https://backpackforlaravel.com/docs/crud-operation-list-entries
     * @return void
     */
    protected function setupListOperation()
    {

        CRUD::column('name');
        $this->crud->addButtonFromView('top', 'update_role', 'update_role', 'end');
        $this->crud->addButtonFromModelFunction('line', 'permission', 'permission', 'end');
        /**
         * Columns can be defined using the fluent syntax or array syntax:
         * - CRUD::column('price')->type('number');
         * - CRUD::addColumn(['name' => 'price', 'type' => 'number']); 
         */
    }

    /**
     * Define what happens when the Create operation is loaded.
     * 
     * @see https://backpackforlaravel.com/docs/crud-operation-create
     * @return void
     */
    protected function setupCreateOperation()
    {
        CRUD::setValidation(RoleRequest::class);
        CRUD::field('name');
        CRUD::addField([
            'name' => 'guard_name',
            'type' => 'hidden',
            'value' => 'web'
        ]);

        /**
         * Fields can be defined using the fluent syntax or array syntax:
         * - CRUD::field('price')->type('number');
         * - CRUD::addField(['name' => 'price', 'type' => 'number'])); 
         */
    }

    /**
     * Define what happens when the Update operation is loaded.
     * 
     * @see https://backpackforlaravel.com/docs/crud-operation-update
     * @return void
     */
    protected function setupUpdateOperation()
    {
        $this->setupCreateOperation();
    }

    function getPermissionOfRole(){
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

    function changeRolePermission(Request $request){
        if($request->input('role') != null){
            // dapatkan id role
            $role = $request->input('role');
            $nameRole = Role::where('id', $role)->first();
                // dapatkan id permission
            DB::beginTransaction();
            try {
                DB::table('role_has_permissions')->where('role_id', $role)->delete();
                if($request->input('permission') != null){
                    $insertData = collect($request->input('permission'))->map(function($value) use($role){
                        return ['permission_id' => $value, 'role_id' => $role];
                    });
                    DB::table('role_has_permissions')->insert($insertData->values()->all());
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
            // }else{
            //     // jika permission kosong
            //     return response()->json([
            //         'status' => false,
            //         'alert' => 'warning',
            //         'message' => 'Permission tidak ada yang dipilih'
            //     ], 200);
            // }
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
}
