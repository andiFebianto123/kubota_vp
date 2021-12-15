<?php

namespace App\Http\Controllers\Admin;

use App\Http\Requests\UserRequest;
use App\Models\UserOtp;
use Backpack\CRUD\app\Http\Controllers\CrudController;
use Backpack\CRUD\app\Library\CrudPanel\CrudPanelFacade as CRUD;
use Exception;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Request;
use Prologue\Alerts\Facades\Alert;
use Spatie\Permission\Models\Role as RoleSpatie;


/**
 * Class UserCrudController
 * @package App\Http\Controllers\Admin
 * @property-read \Backpack\CRUD\app\Library\CrudPanel\CrudPanel $crud
 */
class UserCrudController extends CrudController
{
    use \Backpack\CRUD\app\Http\Controllers\Operations\ListOperation;
    use \Backpack\CRUD\app\Http\Controllers\Operations\CreateOperation;
    use \Backpack\CRUD\app\Http\Controllers\Operations\UpdateOperation;
    use \Backpack\CRUD\app\Http\Controllers\Operations\DeleteOperation;
    use \Backpack\CRUD\app\Http\Controllers\Operations\ShowOperation;

    /**
     * Configure the CrudPanel object. Apply settings to all operations.
     * 
     * @return void
     */
    public function setup()
    {
        CRUD::setModel(\App\Models\User::class);
        CRUD::setRoute(config('backpack.base.route_prefix') . '/user');
        CRUD::setEntityNameStrings('user', 'users');
        $this->crud->query = $this->crud->query
        ->leftJoin('model_has_roles', 'users.id', '=', 'model_has_roles.model_id')
        ->leftJoin('roles', 'model_has_roles.role_id', '=', 'roles.id')
        ->select('users.*', 'roles.name as nama_role');
    }

    /**
     * Define what happens when the List operation is loaded.
     * 
     * @see  https://backpackforlaravel.com/docs/crud-operation-list-entries
     * @return void
     */
    protected function setupListOperation()
    {
        $this->crud->removeButton('show');
        CRUD::column('name');
        CRUD::column('username');
        CRUD::column('email');
        CRUD::addColumn([
            'label'     => 'Vendor', // Table column heading
            'name'      => 'vendor_id', // the column that contains the ID of that connected entity;
            'entity'    => 'vendor', 
            'type' => 'relationship',
            'attribute' => 'vend_num',
        ]);
        CRUD::addColumn([
            'label' => 'Role',
            'name' => 'nama_role',
            'type' => 'model_function',
            'function_name' => 'showRole',
            'searchLogic' => function($query, $column, $searchTerm){
                $query->orWhere('roles.name', 'like', "%{$searchTerm}%");
            }
        ]);

        // CRUD::addColumn([
        //     'label'     => 'Role', // Table column heading
        //     'name'      => 'role_id', // the column that contains the ID of that connected entity;
        //     'entity'    => 'role', 
        //     'type' => 'relationship',
        //     'attribute' => 'name',
        // ]);
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
        CRUD::setValidation(UserRequest::class);
       
        // $this->crud->addField([
        //     'label'     => 'Role', // Table column heading
        //     'type'      => 'select',
        //     'name'      => 'role_id', // the column that contains the ID of that connected entity;
        //     'entity'    => 'role', // the method that defines the relationship in your Model
        //     'attribute' => 'name', // foreign key attribute that is shown to user
        //     'model'     => "App\Models\Role",
        // ]);


        $this->crud->addField([
            'label' => 'Role',
            'name' => 'roles',
            'attribute' => 'name',
            'multiple' => false,
            'pivot' => false,
        ]);

        
        $this->crud->addField([
            'label'     => 'Vendor', // Table column heading
            'type'      => 'select2',
            'name'      => 'vendor_id', // the column that contains the ID of that connected entity;
            'entity'    => 'vendor', // the method that defines the relationship in your Model
            'attribute' => 'vend_num', // foreign key attribute that is shown to user
            'model'     => "App\Models\Vendor",
        ]);
        CRUD::field('name');
        CRUD::field('username');
        CRUD::field('email');
        CRUD::field('password');

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

    public function store(Request $request)
    {
        $this->crud->setRequest($this->crud->validateRequest());

        $request = $this->crud->getRequest();

        // Encrypt password if specified.
        if ($request->input('password')) {
            $request->request->set('password', bcrypt($request->input('password')));
        } 
        $this->crud->setRequest($request);
        $this->crud->unsetValidation(); // Validation has already been run

        $role = $request->input('roles');

        // hapus key roles nya
        unset($this->crud->getStrippedSaveRequest()['roles']);
        // insert data usert
        $item = $this->crud->create($this->crud->getStrippedSaveRequest());
        // setelah insert tambahkan rolenya
        $item->assignRole(RoleSpatie::where('id', $role)->first());

        Alert::success(trans('backpack::crud.insert_success'))->flash();

        // save the redirect choice for next time
        $this->crud->setSaveAction();

        return $this->crud->performSaveAction($item->getKey());
    }


    function update($id)
    {
        $this->crud->setRequest($this->crud->validateRequest());

        /** @var \Illuminate\Http\Request $request */
        $request = $this->crud->getRequest();

        if ($request->input('password')) {
            $request->request->set('password', bcrypt($request->input('password')));
        } else {
            $request->request->remove('password');
        }

        // dd($request);
        $this->crud->setRequest($request);
        $this->crud->unsetValidation(); // Validation has already been run

        $role = $request->input('roles');

        $id_user = $request->get($this->crud->model->getKeyName());

        $getUsers = $this->crud->model::where('id', $id_user)->first();

        // dd([ 
        //     'id' => $request->get($this->crud->model->getKeyName()), 
        //     'update' => $this->crud->getStrippedSaveRequest()
        // ]);

        DB::table('model_has_roles')->where('model_id', $id_user)->delete();

        $getUsers->assignRole(RoleSpatie::where('id', $role)->first());

        unset($this->crud->getStrippedSaveRequest()['roles']);
        
        $item = $this->crud->update($request->get($this->crud->model->getKeyName()),
        $this->crud->getStrippedSaveRequest());
        $this->data['entry'] = $this->crud->entry = $item;
        Alert::success(trans('backpack::crud.update_success'))->flash();

        // save the redirect choice for next time
        $this->crud->setSaveAction();

        return $this->crud->performSaveAction($item->getKey());
    }


    public function destroy($id)
    {
        $this->crud->hasAccessOrFail('delete');

        $id = $this->crud->getCurrentEntryId() ?? $id;

        DB::beginTransaction();
        try{
            if(UserOtp::where('user_id', $id)->exists()){
                UserOtp::where('user_id', $id)->delete();
            }
            $response = $this->crud->delete($id);
            DB::commit();
            return $response;
        }
        catch(Exception $e){
            DB::rollback();
            throw $e;
        }
    }

   
}
