<?php

namespace App\Http\Controllers\Admin;

use App\Http\Requests\UserRequest;
use App\Models\UserOtp;
use Backpack\CRUD\app\Http\Controllers\CrudController;
use Backpack\CRUD\app\Library\CrudPanel\CrudPanelFacade as CRUD;
use App\Helpers\EmailLogWriter;
use Exception;
use App\Imports\UserMasterImport;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Request;
use Prologue\Alerts\Facades\Alert;
use Spatie\Permission\Models\Role as RoleSpatie;
use App\Helpers\Constant;
use App\Models\Vendor;
use App\Models\User;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\TemplateUserExport;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\Request as requests;
use Illuminate\Support\Facades\Mail;
use App\Mail\MailNewUser;
use App\Models\Delivery;
use App\Models\PurchaseOrderLine;
use App\Models\Role;
use Illuminate\Routing\Route;
use App\Rules\IsValidPassword;

class UserCrudController extends CrudController
{
    use \Backpack\CRUD\app\Http\Controllers\Operations\ListOperation;
    use \Backpack\CRUD\app\Http\Controllers\Operations\CreateOperation;
    use \Backpack\CRUD\app\Http\Controllers\Operations\UpdateOperation;
    use \Backpack\CRUD\app\Http\Controllers\Operations\DeleteOperation;

    public function setup()
    {
        CRUD::setModel(User::class);
        CRUD::setRoute(config('backpack.base.route_prefix') . '/user');
        CRUD::setEntityNameStrings('user', 'users');
        $this->crud->query = $this->crud->query
                            ->leftJoin('model_has_roles', 'users.id', '=', 'model_has_roles.model_id')
                            ->leftJoin('roles', 'model_has_roles.role_id', '=', 'roles.id')
                            ->select('users.*', 'roles.name as nama_role');

        if(Constant::checkPermission('Read User')){
            $this->crud->allowAccess('list');
        }else{
            $this->crud->denyAccess('list');
        }        
    }


    protected function setupListOperation()
    {
        $this->crud->removeButton('show');
        if(!Constant::checkPermission('Update User')){
            $this->crud->removeButton('update');
        }
        if(!Constant::checkPermission('Create User')){
            $this->crud->removeButton('create');
        }
        if(!Constant::checkPermission('Delete User')){
            $this->crud->removeButton('delete');
        }

        CRUD::column('name');
        CRUD::column('username');
        CRUD::column('email');
        CRUD::addColumn([
            'label'     => 'Vendor', 
            'name'      => 'vendor_id',
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
        if(in_array(Constant::getRole(),['Admin PTKI'])){
            $this->crud->addButtonFromView('top', 'upload_user', 'upload_user', 'end');
        }else{
            $this->crud->addClause('where', 'vendor_id', '=', backpack_auth()->user()->vendor->id);
        }

        $this->crud->setListView('crud::list-user');
    }

    
    protected function setupCreateOperation()
    {
        if(!Constant::checkPermission('Create User')){
            $this->crud->denyAccess('create');
        }
        $allowNullVendor = false;
        if(in_array(Constant::getRole(),['Admin PTKI'])){
            $allowNullVendor = true;
        }
        CRUD::setValidation(UserRequest::class); 

        $this->crud->addField([   // select2_from_array
            'label' => 'Role',
            'name' => 'roles',
            'attribute' => 'name',
            'pivot' => false,
            'multiple' => false,
            'type' => 'relationship.relationship_select_roles',
            'placeholder' => 'Select an entry',
            'options'     =>  $this->optRoles(),
        ]);
        $this->crud->addField([   // select2_from_array
            'name'        => 'vendor_id',
            'label'       => "Vendor",
            'type'        => 'select2_from_array',
            'options'     => $this->optVendors(),
            'allows_null' => $allowNullVendor,
        ]);
        CRUD::field('name');
        CRUD::field('username');
        CRUD::field('email');
        CRUD::field('password');
    }


    private function optVendors()
    {
        if(in_array(Constant::getRole(),['Admin PTKI'])){
            $vendors = Vendor::get();
        }else{
            $vendors = Vendor::where('id', backpack_auth()->user()->vendor->id)->get();
        }
        $arrVendor = [];
        foreach ($vendors as $key => $v) {
            $arrVendor[$v->id] = $v->vend_num.'-'.$v->vend_name;
        }

        return $arrVendor;
    }


    private function optRoles()
    {
        if(in_array(Constant::getRole(),['Admin PTKI'])){
            $roles = Role::get();
        }else{
            $roles = Role::where('name', '!=', 'Admin PTKI')->get();
        }
        $arrRoles = [];
        foreach ($roles as $key => $r) {
            $arrRoles[$r->id] = $r->name;
        }

        return $arrRoles;
    }


    private function handlePermissionNonAdmin($vendorId){
        $allowAccess = false;

        if(in_array(Constant::getRole(),['Admin PTKI'])){
            $allowAccess = true;
        }else{
            if (backpack_auth()->user()->vendor->id == $vendorId) {
                $allowAccess = true;
            }
        }

        return $allowAccess;
    }


    protected function setupUpdateOperation()
    {
        $vendorId = $this->crud->getCurrentEntry()->vendor_id;

        if($this->handlePermissionNonAdmin($vendorId)){
            $this->setupCreateOperation();
        }else{
            abort(404);
        }
    }


    public function store(Request $request)
    {
        $this->crud->setRequest($this->crud->validateRequest());

        $request = $this->crud->getRequest();

        $request->validate([
            'password' => ['required' , new IsValidPassword()]
        ]);

        if ($request->input('password')) {
            $request->request->set('password', bcrypt($request->input('password')));
        } 
        $this->crud->setRequest($request);
        $this->crud->unsetValidation();

        $role = $request->input('roles');

        unset($this->crud->getStrippedSaveRequest()['roles']);
        $item = $this->crud->create($this->crud->getStrippedSaveRequest());
        $item->assignRole(RoleSpatie::where('id', $role)->first());

        Alert::success(trans('backpack::crud.insert_success'))->flash();

        $this->crud->setSaveAction();

        return $this->crud->performSaveAction($item->getKey());
    }


    function update($id)
    {
        $vendorId = $this->crud->getCurrentEntry()->vendor_id;

        if(!$this->handlePermissionNonAdmin($vendorId)){
            abort(404);
        }

        $this->crud->setRequest($this->crud->validateRequest());

        $request = $this->crud->getRequest();

        if ($request->input('password')) {
            $request->validate(
                [
                    'password' => new IsValidPassword()
                ]
            );
            $request->request->set('password', bcrypt($request->input('password')));
        } else {
            $request->request->remove('password');
        }

        $this->crud->setRequest($request);
        $this->crud->unsetValidation();

        $role = $request->input('roles');

        $userId = $request->get($this->crud->model->getKeyName());

        $getUsers = $this->crud->model::where('id', $userId)->first();

        DB::table('model_has_roles')->where('model_id', $userId)->delete();

        $getUsers->assignRole(RoleSpatie::where('id', $role)->first());

        unset($this->crud->getStrippedSaveRequest()['roles']);
        
        $item = $this->crud->update($request->get($this->crud->model->getKeyName()),
        $this->crud->getStrippedSaveRequest());
        $this->data['entry'] = $this->crud->entry = $item;
        Alert::success(trans('backpack::crud.update_success'))->flash();

        $this->crud->setSaveAction();

        return $this->crud->performSaveAction($item->getKey());
    }


    public function destroy($id)
    {
        $vendorId = $this->crud->getCurrentEntry()->vendor_id;
        if(!$this->handlePermissionNonAdmin($vendorId)){
            abort(404);
        }

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


    public function templateUsers()
    {
        $filename =  'template-users-'.date('YmdHis').'.xlsx';
        return Excel::download(new TemplateUserExport(backpack_auth()->user()), $filename);
    }

    
    public function import(requests $request){

        $validator = Validator::make($request->all(), [
            'file' => 'required|mimes:xlsx,xls',
         ]);

        if ($validator->fails()) {
            $errors = $validator->errors();
            $messageErrors = [];
            foreach ($errors->all() as $message) {
                array_push($messageErrors, $message);
            }
            $messageErrors = implode('<br/>', $messageErrors);
            return response()->json([
                'status' => false,
                'validator' => true,
                'message' => $messageErrors
            ], 200);
        }

        $file = $request->file('file');
		$filename = rand().$file->getClientOriginalName();
        $file->storeAs('public/file_user', $filename);

        DB::beginTransaction();
        try{
            $import = new UserMasterImport();
            $import->import(storage_path('/app/public/file_user/'.$filename));

            if(file_exists( storage_path('/app/public/file_user/'.$filename))) {
                unlink(storage_path('/app/public/file_user/'.$filename));
            }
    
            if(count($import->errorsMessage) > 0){
                DB::rollback();
                return response()->json([
                    'data' => $import->errorsMessage,
                    'status' => false,
                    'message' => 'Ada data yang error ketika di import',
                    'notification' => 'Ada beberapa data tidak valid proses import',
                ], 200);
            }

            if(count($import->dataUsers) > 0){
                foreach($import->dataUsers as $user){
                    try{
                        Mail::to($user['email'])
                        ->send(new MailNewUser($user));
                    }
                    catch(Exception $e){
                        $subject = "Data Error User Import"
                        (new EmailLogWriter())->create($subject, $user['email'], $e->getMessage());
                        DB::commit();
                    }
                }
            }

            DB::commit();
            return response()->json([
                'status' => true,
                'message' => 'Import User telah berhasil dilakukan',
                'notification' => 'File berhasil di import',
            ], 200);

        }catch(\Exception $e){
            DB::rollback();
            Alert::add('error', $e->getMessage())->flash();
        }
    }

   
}

