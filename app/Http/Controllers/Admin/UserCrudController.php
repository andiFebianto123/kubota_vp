<?php

namespace App\Http\Controllers\Admin;

use Exception;
use App\Models\Role;
use App\Models\User;
use App\Models\Vendor;
use App\Models\UserOtp;
use App\Models\Delivery;
use App\Helpers\Constant;
use App\Mail\MailNewUser;
use App\Library\ExportXlsx;
use App\Models\ModelHasRole;
use App\Models\LockedAccount;
use Illuminate\Routing\Route;
use App\Rules\IsValidPassword;
use App\Helpers\EmailLogWriter;
use App\Imports\UserMasterImport;
use App\Models\PurchaseOrderLine;
use App\Exports\TemplateExportAll;
use App\Http\Requests\UserRequest;
use Illuminate\Support\Facades\DB;
use Prologue\Alerts\Facades\Alert;
use App\Exports\TemplateUserExport;
use Illuminate\Support\Facades\Mail;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Facades\Request;
use Illuminate\Support\Facades\Storage;
use Box\Spout\Common\Entity\Style\Color;
use Illuminate\Http\Request as requests;
use Illuminate\Support\Facades\Validator;
// export with spout
use Box\Spout\Common\Entity\Style\CellAlignment;
use Spatie\Permission\Models\Role as RoleSpatie;
use Backpack\CRUD\app\Http\Controllers\CrudController;
use Box\Spout\Writer\Common\Creator\Style\StyleBuilder;
use Backpack\CRUD\app\Library\CrudPanel\CrudPanelFacade as CRUD;

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
                            ->join('model_has_roles', 'users.id', '=', 'model_has_roles.model_id')
                            ->join('roles', 'model_has_roles.role_id', '=', 'roles.id')
                            ->select('users.*', 'roles.name as nama_role');

        if(Constant::checkPermission('Read User')){
            $this->crud->allowAccess('active');
            $this->crud->allowAccess('list');
        }else{
            $this->crud->denyAccess('list');
        }        
        $this->crud->allowAccess('advanced_export_excel');
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

        $this->crud->addButtonFromView('line', 'active_inactive', 'active_inactive', 'beginning');

        if(Constant::checkPermission('Access Reset Password')){
            $this->crud->addButtonFromView('line', 'mail_reset_password', 'mail_reset_password', 'beginning');
        }

        if(Constant::checkPermission('Access Reset Attempt Login')){
            $this->crud->addButtonFromView('line', 'reset_attempt_login', 'reset_attempt_login', 'beginning');
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
            'searchLogic' => function($query, $column, $searchTerm){
                $query->orWhereHas('vendor', function($q) use ($searchTerm){
                    $q->where('vend_num', 'like', "%{$searchTerm}%");
                });
            }
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
        if(strpos(strtoupper(Constant::getRole()), 'PTKI')){
            $this->crud->addButtonFromView('top', 'upload_user', 'upload_user', 'end');
        }else{
            $this->crud->addClause('where', 'vendor_id', '=', backpack_auth()->user()->vendor->id);
        }

        CRUD::addColumn([
            'label' => 'Status',
            'name' => 'is_active',
            'type' => 'closure',
            'orderable' => false,
            'function' => function($entry){
                if($entry->is_active){
                    return '<span class="text">Active</span>';
                }
                return '<span class="text">Inactive</span>';
            },
        ]);

        $this->crud->exportRoute = url('admin/user-export');
        $this->crud->addButtonFromView('top', 'advanced_export_excel', 'advanced_export_excel', 'end');

        // $this->crud->addButtonFromModelFunction('top', 'excel_export_advance', 'excelExportAdvance', 'end');

        $this->crud->setListView('crud::list-user');
    }

    
    protected function setupCreateOperation()
    {
        if(!Constant::checkPermission('Create User')){
            $this->crud->denyAccess('create');
        }
        $allowNullVendor = false;
        if(strpos(strtoupper(Constant::getRole()), 'PTKI')){
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
        CRUD::field('password')->type('show_password');
        CRUD::addField([   // select_from_array
            'name'        => 'is_active',
            'label'       => "Status",
            'type'        => 'select2_from_array',
            'options'     => ['1' => 'Yes', '0' => 'No'],
            'allows_null' => true, 
            // 'allows_multiple' => true, // OPTIONAL; needs you to cast this to array in your model;
        ]);
    }


    private function optVendors()
    {
        if(strpos(strtoupper(Constant::getRole()), 'PTKI')){
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
        if(strpos(strtoupper(Constant::getRole()), 'PTKI')){
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

        if(strpos(strtoupper(Constant::getRole()), 'PTKI')){
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

        // $modelRoles = ModelHasRole::where('model_id', $userId)->get();
        // if($modelRoles->count() > 0){
        //     $modelRoles->delete();
        // }

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
                        Mail::to(str_replace(" ", "", $user['email']))
                        ->send(new MailNewUser($user));
                    }
                    catch(Exception $e){
                        $subject = "Data Error User Import";
                        (new EmailLogWriter())->create($subject, $user['email'], $e->getMessage(), '', env('MAIL_USER_BCC',""), env('MAIL_REPLY_TO',""));
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

    function activeInactive($id){
        $this->crud->hasAccessOrFail('active');
        $user = User::where('id', $id)->first();
        if($user != null){
            if($user->is_active){
                $user->is_active = 0;
            }else{
                $user->is_active = 1;
            }
            $user->save();
            return response()->json([
                'status' => true,
                'message' => 'Success to change status acount',
            ]);
        }
        return response()->json([
            'status' => false,
            'message' => 'Is acount is not found',
        ], 403);
    }

    public function search()
    {
        $this->crud->hasAccessOrFail('list');

        $this->crud->applyUnappliedFilters();

        $totalRows = $this->crud->model->count();
        $filteredRows = $this->crud->query->toBase()->getCountForPagination();
        $startIndex = request()->input('start') ?: 0;
        // if a search term was present
        if (request()->input('search') && request()->input('search')['value']) {
            // filter the results accordingly
            $this->crud->applySearchTerm(request()->input('search')['value']);
            // recalculate the number of filtered rows
            $filteredRows = $this->crud->count();
        }
        // start the results according to the datatables pagination
        if (request()->input('start')) {
            $this->crud->skip((int) request()->input('start'));
        }
        // limit the number of results according to the datatables pagination
        if (request()->input('length')) {
            $this->crud->take((int) request()->input('length'));
        }
        // overwrite any order set in the setup() method with the datatables order
        if (request()->input('order')) {
            // clear any past orderBy rules
            $this->crud->query->getQuery()->orders = null;
            foreach ((array) request()->input('order') as $order) {
                $column_number = (int) $order['column'];
                $column_direction = (strtolower((string) $order['dir']) == 'asc' ? 'ASC' : 'DESC');
                $column = $this->crud->findColumnById($column_number);
                if ($column['tableColumn'] && ! isset($column['orderLogic'])) {
                    // apply the current orderBy rules
                    $this->crud->orderByWithPrefix($column['name'], $column_direction);
                }

                // check for custom order logic in the column definition
                if (isset($column['orderLogic'])) {
                    $this->crud->customOrderBy($column, $column_direction);
                }
            }
        }

        // show newest items first, by default (if no order has been set for the primary column)
        // if there was no order set, this will be the only one
        // if there was an order set, this will be the last one (after all others were applied)
        // Note to self: `toBase()` returns also the orders contained in global scopes, while `getQuery()` don't.
        $orderBy = $this->crud->query->toBase()->orders;
        $table = $this->crud->model->getTable();
        $key = $this->crud->model->getKeyName();

        $hasOrderByPrimaryKey = collect($orderBy)->some(function ($item) use ($key, $table) {
            return (isset($item['column']) && $item['column'] === $key)
                || (isset($item['sql']) && str_contains($item['sql'], "$table.$key"));
        });

        if (! $hasOrderByPrimaryKey) {
            $this->crud->orderByWithPrefix($this->crud->model->getKeyName(), 'DESC');
        }

        $entries = $this->crud->getEntries();

        $dbStatement = getSQL($this->crud->query);

        session(["sqlSyntax" => $dbStatement]);

        return $this->crud->getEntriesAsJsonForDatatables($entries, $totalRows, $filteredRows, $startIndex);
    }

    public function exportAdvance(){
        if(session()->has('sqlSyntax')){
            $sqlQuery = session('sqlSyntax');
            $pattern = '/((limit+\s+[0-9]+)|(offset+\s+[0-9]+))/i';
            $query = preg_replace($pattern, "", $sqlQuery);
            $datas = DB::select($query);

            $filename = 'USER-'.date('YmdHis').'.xlsx';

            $resultCallback = function($result){
                return [
                    'no' => '<number>',
                    'name' => $result->name,
                    'username' => $result->username,
                    'email' => $result->email,
                    'vendor' => function($result){
                        $vendor = Vendor::where('id', $result->vendor_id)->first();
                        if($vendor == null){
                            return '-';
                        }
                        return $vendor->vend_num;
                    },
                    'role' => $result->nama_role,
                ];
            };

            // $GLOBALS['col'] = '<cols>';
            // $GLOBALS['col'] .= '<col min="1" max="1" width="10" customWidth="1"/>';
            // $GLOBALS['col'] .= '<col min="2" max="2" width="15" customWidth="1"/>';
            // $GLOBALS['col'] .= "</cols>";
    
            $export = new ExportXlsx($filename);
    
            $styleForHeader = (new StyleBuilder())
                            ->setFontBold()
                            ->setFontColor(Color::WHITE)
                            ->setCellAlignment(CellAlignment::LEFT)
                            ->setBackgroundColor(Color::rgb(102, 171, 163))
                            ->build();
    
            $firstSheet = $export->currentSheet();
    
            $export->addRow(['No', 
                'Name',
                'Username',
                'Email',
                'Vendor',
                'Role',
            ], $styleForHeader);

            $styleForBody = (new StyleBuilder())
                            ->setFontColor(Color::BLACK)
                            ->setCellAlignment(CellAlignment::LEFT)
                            ->build();

            $increment = 1;
            foreach($datas as $data){
                $row = $resultCallback($data);
                $rowT = [];
                foreach($row as $key => $value){
                    if($value == "<number>"){
                        $rowT[] = $increment;
                    }else if(is_callable($value)){
                        $rowT[] = $value($data);
                    }else{
                        $rowT[] = $value;
                    }
                }
                $increment++;
                $export->addRow($rowT, $styleForBody);
            }

            $export->close();
        }
    }

    public function exportAdvance2(Request $request){
        if(session()->has('sqlSyntax')){
            $sqlQuery = session('sqlSyntax');
            $pattern = '/((limit+\s+[0-9]+)|(offset+\s+[0-9]+))/i';
            $query = preg_replace($pattern, "", $sqlQuery);
            $data = DB::select($query);

            $filename = 'USER-'.date('YmdHis').'.xlsx';

            $title = "Report Users";

            $header = [
                'no' => 'No',
                'name' => 'Name',
                'username' => 'Username',
                'email' => 'Email',
                'vendor' => 'Vendor',
                'role' => 'Role',
            ];

            $resultCallback = function($result){
                return [
                    'no' => '<number>',
                    'name' => $result->name,
                    'username' => $result->username,
                    'email' => $result->email,
                    'vendor' => function($result){
                        $vendor = Vendor::where('id', $result->vendor_id)->first();
                        if($vendor == null){
                            return '-';
                        }
                        return $vendor->vend_num;
                    },
                    'role' => $result->nama_role,
                ];
            };

            $styleHeader = function(\Maatwebsite\Excel\Events\AfterSheet $event){
                $styleHeader = [
                    //Set font style
                    'font' => [
                        'bold'      =>  true,
                        'color' => ['argb' => 'ffffff'],
                    ],
        
                    //Set background style
                    'fill' => [
                        'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                        'startColor' => [
                            'rgb' => '66aba3',
                         ]           
                    ],
        
                ];

                $styleGroupProtected = [
                    //Set background style
                    'fill' => [
                        'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                        'startColor' => [
                            'rgb' => 'ededed',
                         ]           
                    ],
        
                ];

                $arrColumns = range('A', 'F');
                foreach ($arrColumns as $key => $col) {
                    $event->sheet->getColumnDimension($col)->setAutoSize(true);
                    $event->sheet->getStyle($col.'1')->getFont()->setBold(true);
                }
                
                $event->sheet->getDelegate()->getStyle('A1:F1')->applyFromArray($styleHeader);
            };

           

            return Excel::download(new TemplateExportAll($data, $header, $resultCallback, $styleHeader, $title), $filename);
        }
        return 0;
    }


    public function resetAttemptLogin($id){
        DB::beginTransaction();
        try{
            $user = User::where('id', $id)->first();
            if($user == null){
                DB::rollback();
                return response()->json(['message' => 'User tidak ditemukan'], 404);
            }
            $now = now();

            $las = LockedAccount::where('account', $user->username)
            ->where('type', 'login')
            ->where('lock_end', '>', $now)
            ->get();
            foreach($las as $la){
                $la->delete();
            }

            $las = LockedAccount::where('account', $user->email)
            ->where('type', 'login')
            ->where('lock_end', '>', $now)
            ->get();
            foreach($las as $la){
                $la->delete();
            }

            DB::commit();
            return response()->json([
                'status' => true,
                'alert' => 'success',
                'message' => 'Berhasil melakukan reset attempt login']);
        }
        catch(Exception $e){
            DB::rollback();
            throw $e;
        }
    }

   
}

