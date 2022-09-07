<?php

namespace App\Http\Controllers\Admin;

use Maatwebsite\Excel\Facades\Excel;
use App\Exports\TemplateExportAll;
use App\Http\Requests\VendorRequest;
use Illuminate\Http\Request;
use Backpack\CRUD\app\Http\Controllers\CrudController;
use Backpack\CRUD\app\Library\CrudPanel\CrudPanelFacade as CRUD;
use Prologue\Alerts\Facades\Alert;
use Illuminate\Support\Facades\DB;
use App\Helpers\Constant;
use App\Models\Vendor;

use App\Library\ExportXlsx;
use Box\Spout\Writer\Common\Creator\Style\StyleBuilder;
use Box\Spout\Common\Entity\Style\CellAlignment;
use Box\Spout\Common\Entity\Style\Color;

class VendorCrudController extends CrudController
{
    use \Backpack\CRUD\app\Http\Controllers\Operations\ListOperation;
    use \Backpack\CRUD\app\Http\Controllers\Operations\CreateOperation;
    use \Backpack\CRUD\app\Http\Controllers\Operations\UpdateOperation;
    use \Backpack\CRUD\app\Http\Controllers\Operations\DeleteOperation;

    public function setup()
    {
        CRUD::setModel(Vendor::class);
        CRUD::setRoute(config('backpack.base.route_prefix') . '/vendor');
        CRUD::setEntityNameStrings('vendor', 'vendors');
        if(Constant::checkPermission('Read Vendor')){
           $this->crud->allowAccess('list'); 
        }else{
            $this->crud->denyAccess('list');
        }
        $this->crud->allowAccess('advanced_export_excel');
    }


    protected function setupListOperation()
    {
        $this->crud->removeButton('show');

        if(!Constant::checkPermission('Update Vendor')){
            $this->crud->removeButton('update');
        }
        if(!Constant::checkPermission('Create Vendor')){
            $this->crud->removeButton('create');
        }
        if(!Constant::checkPermission('Delete Vendor')){
            $this->crud->removeButton('delete');
        }

        CRUD::addColumn([
            'label'     => 'Number', 
            'name'      => 'vend_num', 
            'type' => 'text',
        ]);
        CRUD::addColumn([
            'label'     => 'Vendor Name', 
            'name'      => 'vend_name', 
            'type' => 'text',
        ]);
        CRUD::addColumn([
            'label'     => 'Vendor Email', 
            'name'      => 'vend_email', 
            'type' => 'text',
        ]);
        CRUD::addColumn([
            'label'     => 'Buyer Name', 
            'name'      => 'buyer', 
            'type' => 'text',
        ]);
        CRUD::addColumn([
            'label'     => 'Buyer Email', 
            'name'      => 'buyer_email', 
            'type' => 'text',
        ]);
        CRUD::addColumn([
            'label'     => 'Address', 
            'name'      => 'vend_addr', 
            'type' => 'text',
        ]);
        CRUD::addColumn('currency');
        CRUD::column('created_at');
        CRUD::column('updated_at');
        if(strpos(strtoupper(Constant::getRole()), 'PTKI')){
            $this->crud->addFilter([
                'name'        => 'vendor',
                'type'        => 'select2_ajax',
                'label'       => 'Vendor Name',
                'placeholder' => 'Pick a vendor'
            ],
            url('filter-vendor/ajax-itempo-options'),
            function($value) { 
                $this->crud->addClause('where', 'vend_num', $value);
            });
        }else{
            $this->crud->removeButton('create');
            $this->crud->addClause('where', 'id', '=', backpack_auth()->user()->vendor->id);
        }
        $this->crud->exportRoute = url('vendor-export');
        $this->crud->addButtonFromView('top', 'advanced_export_excel', 'advanced_export_excel', 'end');
        // $this->crud->addButtonFromModelFunction('top', 'excel_export_advance', 'excelExportAdvance', 'end');
    }


    private function handlePermissionNonAdmin($vendor_id){
        $allowAccess = false;

        if(strpos(strtoupper(Constant::getRole()), 'PTKI')){
            $allowAccess = true;

        }else{
            if (backpack_auth()->user()->vendor->id == $vendor_id) {
                $allowAccess = true;
            }
        }

        return $allowAccess;
    }

   
    protected function setupCreateOperation()
    {
        if(!Constant::checkPermission('Create Vendor')){
            $this->crud->denyAccess('create');
        }
        CRUD::setValidation(VendorRequest::class);
        $this->myFields('create');
    }


    protected function setupUpdateOperation()
    {
        $id = $this->crud->getCurrentEntry()->id;

        if(!$this->handlePermissionNonAdmin($id)){
            abort(404);
        }

        CRUD::setValidation(VendorRequest::class);
        $this->myFields('update');
    }


    private function myFields($fieldFor){
        CRUD::addField([
            'label'     => 'Number', 
            'name'      => 'vend_num', 
            'type' => 'text',
        ]);
        CRUD::addField([
            'label'     => 'Vendor Name', 
            'name'      => 'vend_name', 
            'type' => 'text',
        ]);
        CRUD::addField([
            'label'     => 'Vendor Email', 
            'name'      => 'vend_email', 
            'type' => 'text',
        ]);
        CRUD::addField([
            'label'     => 'Buyer Name', 
            'name'      => 'buyer', 
            'type' => 'text',
        ]);
        CRUD::addField([
            'label'     => 'Buyer Email', 
            'name'      => 'buyer_email', 
            'type' => 'text',
        ]);
        CRUD::addField([
            'label'     => 'Address', 
            'name'      => 'vend_addr', 
            'type' => 'text',
        ]);
        $attr = [];
        if ($fieldFor == 'update') {
            $attr = ['disabled' => 'disabled'];
        }
        CRUD::addField([
            'label'     => 'Currency', 
            'name'      => 'currency', 
            'type' => 'text',
            'attributes' => $attr 
        ]);
    }


    public function itemVendorOptions(Request $request){
        $term = $request->input('term');
        return Vendor::where('vend_name', 'like', '%'.$term.'%')
            ->orWhere('vend_num', 'like', '%'.$term.'%')
        ->select('vend_num', 'vend_name')
        ->get()
        ->mapWithKeys(function($vendor){
            return [$vendor->vend_num => $vendor->vend_num.' - '.$vendor->vend_name];
        });
    }
    

    public function itemVendorOptions2(Request $request){
        $term = $request->input('term');
        return Vendor::where('vend_name', 'like', '%'.$term.'%')
        ->orWhere('vend_num', 'like', '%'.$term.'%')
        ->get()
        ->mapWithKeys(function($vendor){
            return [$vendor->vend_num => $vendor->vend_num.' - '.$vendor->vend_name];
        });
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
          
            $resultCallback = function($result){
                return [
                    'no' => '<number>',
                    'number' => $result->vend_num,
                    'vendor_name' => $result->vend_name,
                    'vendor_email' => $result->vend_email,
                    'buyer_name' => $result->buyer,
                    'buyer_email' => $result->buyer_email,
                    'address' => $result->vend_addr,
                    'currency' => $result->currency,
                    'created' => $result->created_at,
                    'updated' => $result->updated_at
                ];
            };

            $filename = 'VENDOR-'.date('YmdHis').'.xlsx';

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
            'Number',
            'Vendor Name',
            'Vendor Email',
            'Buyer Name',
            'Buyer Email',
            'Address',
            'Currency',
            'Created',
            'Updated'
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

            $filename = 'VENDOR-'.date('YmdHis').'.xlsx';

            $title = "Report Vendor";

            $header = [
                'no' => 'No',
                'number' => 'Number',
                'vendor_name' => 'Vendor Name',
                'vendor_email' => 'Vendor Email',
                'buyer_name' => 'Buyer Name',
                'buyer_email' => 'Buyer Email',
                'address' => 'Address',
                'currency' => 'Currency',
                'created' => 'Created',
                'updated' => 'Updated'
            ];

            $resultCallback = function($result){
                return [
                    'no' => '<number>',
                    'number' => $result->vend_num,
                    'vendor_name' => $result->vend_name,
                    'vendor_email' => $result->vend_email,
                    'buyer_name' => $result->buyer,
                    'buyer_email' => $result->buyer_email,
                    'address' => $result->vend_addr,
                    'currency' => $result->currency,
                    'created' => $result->created_at,
                    'updated' => $result->updated_at
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

                $arrColumns = range('A', 'J');
                foreach ($arrColumns as $key => $col) {
                    $event->sheet->getColumnDimension($col)->setAutoSize(true);
                    $event->sheet->getStyle($col.'1')->getFont()->setBold(true);
                }
                
                $event->sheet->getDelegate()->getStyle('A1:J1')->applyFromArray($styleHeader);
            };

           

            return Excel::download(new TemplateExportAll($data, $header, $resultCallback, $styleHeader, $title), $filename);
        }
        return 0;
    }


}
