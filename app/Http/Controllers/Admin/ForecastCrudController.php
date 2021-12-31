<?php

namespace App\Http\Controllers\Admin;

use App\Http\Requests\ForecastRequest;
use Backpack\CRUD\app\Http\Controllers\CrudController;
use Backpack\CRUD\app\Library\CrudPanel\CrudPanelFacade as CRUD;
use Illuminate\Support\Facades\Session;
use App\Helpers\ForecastConverter;
use App\Models\PurchaseOrder;
use App\Models\Forecast;
use Illuminate\Support\Facades\DB;
use App\Mail\vendorNewPo;
use Illuminate\Support\Facades\Mail;
use DateTime;
use App\Helpers\Constant;


/**
 * Class ForecastCrudController
 * @package App\Http\Controllers\Admin
 * @property-read \Backpack\CRUD\app\Library\CrudPanel\CrudPanel $crud
 */
class ForecastCrudController extends CrudController
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
        CRUD::setModel(\App\Models\Forecast::class);
        CRUD::setRoute(config('backpack.base.route_prefix') . '/forecast');
        CRUD::setEntityNameStrings('forecast', 'forecasts');
        if(Constant::checkPermission('Read Forecast')){
            $this->crud->allowAccess('list');
        }else{
            $this->crud->denyAccess('list');
        }
        // $modelForecast = new Forecast();
        // $mm = $modelForecast->select('*');
        // dd($mm->toBase()->getCountForPagination());
        // $this->crud->query = $this->crud->query
    }

    protected function getFieldAccess(){
        if(backpack_user()->hasRole('Admin PTKI')){
            if(request('vendor_submit')){
                if(request('filter_vendor') && request('filter_vendor') != 'hallo'){
                    Session::put("vendor_name", request('filter_vendor'));
                    $db = DB::table('vendor')->where('vend_num', Session::get('vendor_name'))->first();
                    Session::put('vendor_text', $db->vend_name);
                }else{
                    Session::forget('vendor_name');
                    Session::forget('vendor_text');
                }
            }
        }
    }

    protected function setQuery(){
        if(backpack_user()->hasRole('Admin PTKI')){
            if(Session::get('vendor_name')){
                $this->crud->query = $this->crud->query
                ->select('id', 'forecast_num', 'item', 'forecast_date' ,'qty')
                ->where('vend_num', Session::get('vendor_name'))
                ->groupBy('item')
                ->orderBy('id', 'DESC');
            }else{
                $this->crud->query = $this->crud->query
                ->select('id', 'forecast_num', 'item', 'forecast_date' ,'qty')
                ->groupBy('item')
                ->orderBy('id', 'DESC');
            }
        }else{
            // jika vendor biasa
            $this->crud->query = $this->crud->query
            ->select('id', 'forecast_num', 'item', 'forecast_date' ,'qty')
            ->where('vend_num', backpack_user()->vendor->vend_num)
            ->groupBy('item')
            ->orderBy('id', 'DESC');
        }
    }

    /**
     * Define what happens when the List operation is loaded.
     * 
     * @see  https://backpackforlaravel.com/docs/crud-operation-list-entries
     * @return void
     */
    // public function index()
    // {

    // }

    protected function setupListOperation()
    {
        $this->crud->removeButton('create');
        $this->crud->removeButton('update');
        $this->crud->removeButton('delete');
        // dd([
        //     'cek_role_admin' => backpack_user()->hasRole('Admin PTKI'),
        //     'cek_permision_create' => backpack_user()->hasDirectPermission('create'),
        //     'cek_permission_update' => backpack_user()->hasDirectPermission('update'),
        //     'all_direct_permission' => backpack_user()->getDirectPermissions()->values()->all(),
        //     'all_permission_via_role' => backpack_user()->getPermissionsViaRoles()->values()->all(),
        //     'all_permission' => backpack_user()->getAllPermissions()->values()->all(),
        // ]);
        
        // $arr_week = ["Week 1","Week 2", "Week 3", "Week 4"];
        // $arr_day = ["Senin","Selasa", "Rabu", "Kamis", "Jumat", "Sabtu", "Minggu"];
        // $ar_month = ["Januari","Februari", "Maret", "April", "Mei", "Juni", "Juli", "Agustus", "September", "Oktober", "November", "Desember"];
        // CRUD::addColumn([
        //     'label'     => 'Name', // Table column heading
        //     'name'      => 'nama',
        // ]);
        // CRUD::addColumn([
        //     'label'     => 'Age', // Table column heading
        //     'name'      => 'age',
        // ]);

        // $this->crud->allowResponsive();

        $this->getFieldAccess();

        $this->setQuery();

        $forecast = new ForecastConverter;

        if(request("filter_forecast_by") != null){ 
            if(request('filter_forecast_by') == 'day'){
                $forecast->type = 'days';
                Session::put("forecast_type", $forecast->type);
            }else if(request('filter_forecast_by') == 'week'){
                $forecast->type = 'week';
                Session::put("forecast_type", $forecast->type);
            }else{
                $forecast->type = 'moon';
                Session::put("forecast_type", $forecast->type);
            }
        }else{
            $forecast->type = 'days';
            Session::put("forecast_type", $forecast->type);
        }

        $start = $forecast->forecastStart();

        $columns = $start->getColumns();

        $this->crud->columnHeader = $start->columnHeader;

        $this->crud->type = $start->type;

        CRUD::addColumn([
            'label'     => 'Nama Item', // Table column heading
            'name'      => 'name_item',
        ]);
        foreach($columns as $key => $column){
            CRUD::addColumn([
                'label' => ($forecast->type == 'week') ? "{$column['value']}" : $column,
                'name' => "column_" . "{$key}",
                'rome_symbol' => ($forecast->type == 'week') ? $column['rome_symbol'] : '',
                'type' => 'forecast',
                // 'orderable' => false,
            ]);
        }

        $this->crud->urlAjaxFilterVendor = url('admin/test/ajax-vendor-options');
        $this->data['filter_vendor'] = backpack_user()->hasRole('Admin PTKI');
        $this->crud->setListView('vendor.backpack.crud.forecast-list', $this->data);
        
    }

    private function dynamicColumns($ffb)
    {
        // dd($ffb);
        $arr_filter_forecasts = ['day', 'week', 'month', 'year'];

        $arr_filters = [
            'day' => ["Senin", "Selasa", "Rabu", "Kamis", "Jumat", "Sabtu", "Minggu"],
            'week' => ["Week 1", "Week 2", "Week 3", "Week 4"],
            'month' => ["Januari", "Februari", "Maret", "April", "Mei", "Juni", "Juli", "Agustus", "September", "Oktober", "November", "Desember"],
            'year' => [2018, 2019, 2020, 2021],
        ];

        $columns = $arr_filters[$ffb]; // ["Week 1", "Week 2", "Week 3", "Week 4"],
        $index_filter = array_search($ffb, $arr_filter_forecasts); // 1
        $end_url = "";
        $link_enabled = false;
        $session_date = ($index_filter + 1 < sizeof($arr_filter_forecasts))? $arr_filter_forecasts[$index_filter + 1] : "undefined";
        if ($index_filter > 0) {
            $link_enabled = true;
        }
        if ($index_filter + 1 < sizeof($arr_filter_forecasts)) {
            $session_date = $arr_filter_forecasts[$index_filter + 1];

            if (request($session_date) != null) {
                Session::put($session_date, " > ".request($session_date));
            }
        }else{
            foreach ($arr_filter_forecasts as $key => $value) {
                Session::forget($value);
            }
        }
        
        foreach ($columns as $key => $col) {
            $arr_dynamic_col = [
                'label'     => $col, // Table column heading
                'name'      => 'forecast_num_' . $key,
                // 'link'     =>  $end_url,
                'type'     => 'closure',
                'function' => function ($entry) {
                    return $entry->qty;
                }
            ];
            if ($link_enabled) {
                $end_url = "?filter_forecast_by=" . $arr_filter_forecasts[$index_filter - 1] . "&" . $ffb . "=";
                $arr_dynamic_col['link'] = $end_url;
            }

            // CRUD::addColumn($arr_dynamic_col);
        }
        
    }

    /**
     * Define what happens when the Create operation is loaded.
     * 
     * @see https://backpackforlaravel.com/docs/crud-operation-create
     * @return void
     */
    protected function setupCreateOperation()
    {
        CRUD::setValidation(ForecastRequest::class);



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

    public function search(){
        $this->crud->hasAccessOrFail('list');
        // $this->crud->applyUnappliedFilters();
        $totalRows = $this->crud->query->get()->count(); # $this->crud->model->count();
        $filteredRows = $this->crud->query->toBase()->getCountForPagination(); # $this->crud->query->toBase()->getCountForPagination();
        $startIndex = request()->input('start') ?: 0;
        // if a search term was present
        if (request()->input('search') && request()->input('search')['value']) {
            // filter the results accordingly
            // recalculate the number of filtered rows
            // $filteredRows = $this->crud->count();
        }
        // start the results according to the datatables pagination
        if (request()->input('start')) {
            $this->crud->skip((int) request()->input('start'));
        }
        // limit the number of results according to the datatables pagination
        if (request()->input('length')) {
            $this->crud->take((int) request()->input('length'));
        }

        $entries = $this->crud->getEntries();

        $getItem = $entries->map(function($item){
            return $item->item;
        });
        # mengambil semua nama item data dari tabel forecast
        

        $forecast = new ForecastConverter;
        # tambah model tabel forecast
        $forecast->model = $this->crud->model;
        # set nama item kedalam perhitungan forecast
        $forecast->name_items = $getItem->values()->all(); 

        # pilih berdasarkan filter penentuan type perhitungan forecast
        if(Session::get('forecast_type') == 'days'){
            $forecast->type = 'days';
        }else if(Session::get('forecast_type') == 'week'){
            $forecast->type = 'week';
        }else{
            $forecast->type = 'moon';

        }
        // $forecast->getQuery();

        # memulai forecast
        $start = $forecast->forecastStart();
        # menampilkan hasil forecast
        $resultForecast =  $start->getResultForecast();

        // overwrite any order set in the setup() method with the datatables order
        if (request()->input('order')) {
            // clear any past orderBy rules
            // $collectData = collect($start->resultForecastForOriginal);
            // // dd(request()->input('order'));
            $orderBy = array();
            foreach ((array) request()->input('order') as $order) {
                $columnIndex = (int) $order['column'];
                $perfix = $order['dir'];
                array_push($orderBy, [$columnIndex, $perfix]);
            }
            $resultForecast = $start->getResultWithOrderBy($orderBy);
        }
        $callback = array(
            'draw'=>request()->input('draw'), // Ini dari datatablenya untuk tanda pada halaman pagination
            'recordsTotal' => $totalRows, // total dari semua row
            'recordsFiltered' => $filteredRows,
            'data' => $resultForecast,
        );
        return $callback;
    }

    


}
