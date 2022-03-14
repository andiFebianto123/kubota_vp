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
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\ForecastExport;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;



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
    }

    public function create(){
        return abort(404);
    }

    public function edit(){
        return abort(404);
    }

    public function show(){
        return abort(404);
    }

    protected function getFieldAccess(){
        if(backpack_user()->hasRole('Admin PTKI')){
            if(request('vendor_submit')){
                if(request('filter_vendor') && request('filter_vendor') != 'hallo'){
                    Session::put("vendor_name", request('filter_vendor'));
                    $db = DB::table('vendor')->where('vend_num', Session::get('vendor_name'))->first();
                    Session::put('vendor_text', $db->vend_num);
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
        $this->crud->addButtonFromView('bottom', 'print_forecast', 'print_forecast', 'beginning');

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
                $forecast->type = 'month';
                Session::put("forecast_type", $forecast->type);
            }
        }else{
            $forecast->type = 'week';
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

        $this->crud->urlAjaxFilterVendor = url('admin/filter-vendor/ajax-itempo-options2');
        $this->data['filter_vendor'] = backpack_user()->hasRole('Admin PTKI');
        $this->data['type_forecast'] = $start->type;
        if(backpack_user()->hasRole('Admin PTKI')){
            $this->crud->setListView('vendor.backpack.crud.list-forecast', $this->data);
        }else{
            $this->crud->setListView('vendor.backpack.crud.forecast-underconstruction', $this->data);
        }
        
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

    function applySearchForecast(){

        // tahap uji coba search per week

        $forecast = new ForecastConverter;

        if(Session::get('forecast_type') == 'days'){
            $forecast->type = 'days';
        }else if(Session::get('forecast_type') == 'week'){
            $forecast->type = 'week';
        }else{
            $forecast->type = 'month';
        }

        $startForecast = $forecast->forecastStart();

        $columns = $startForecast->getColumns();

        $this->crud->query = $this->crud->model;

        $search = request()->input('search')['value'];

        $whereWithVendor = "";
        $whereWithVendor_ = "";

        if($startForecast->type == 'week'){
            if(Session::get('vendor_name')){
                $whereWithVendor = "AND f.vend_num = '".Session::get('vendor_name')."'";
                $whereWithVendor_ = "AND f_w_.vend_num = '".Session::get('vendor_name')."'";
            }
        
            $this->crud->query = $this->crud->query->whereRaw("
                id = (SELECT MAX(f.id) FROM forecasts f WHERE f.item = forecasts.item AND SUBSTR(f.forecast_date, 1, 10) = SUBSTR(forecasts.forecast_date, 1, 10) {$whereWithVendor})
            ");
            
            $this->crud->query = $this->crud->query->groupBy(DB::raw("item"));
    
            $selectData = [
                'item', 
            ];
    
            $i = 1;
            foreach($columns as $column){
                $raw = DB::raw("(SELECT SUM(qty) FROM forecasts f_w WHERE f_w.item = forecasts.item AND SUBSTR(f_w.forecast_date, 1, 10) BETWEEN '{$column['first_date']}' AND '{$column['last_date']}' AND f_w.id = (SELECT MAX(f_w_.id) FROM forecasts f_w_ WHERE f_w_.item = forecasts.item AND SUBSTR(f_w_.forecast_date, 1, 10) = SUBSTR(f_w.forecast_date, 1, 10) {$whereWithVendor_})) as week{$i}");
                $i++;
                array_push($selectData, $raw);
            }
    
    
            $this->crud->query = $this->crud->query->havingRaw(DB::raw("item LIKE '%{$search}%'"))
            ->orHavingRaw('week1 = ?', [$search])
            ->orHavingRaw('week2 = ?', [$search])
            ->orHavingRaw('week3 = ?', [$search])
            ->orHavingRaw('week4 = ?', [$search])
            ->orHavingRaw('week5 = ?', [$search])
            ->orHavingRaw('week6 = ?', [$search])
            ->orHavingRaw('week7 = ?', [$search])
            ->orHavingRaw('week8 = ?', [$search])
            ->orHavingRaw('week9 = ?', [$search])
            ->orHavingRaw('week10 = ?', [$search])
            ->orHavingRaw('week11 = ?', [$search])
            ->orHavingRaw('week12 = ?', [$search])
            ->orHavingRaw('week13 = ?', [$search])
            ->orHavingRaw('week14 = ?', [$search])
            ->orHavingRaw('week15 = ?', [$search])
            ->orHavingRaw('week16 = ?', [$search])
            ->select($selectData);
            dd($this->crud->query->toSql());
        }else if($startForecast->type == 'days'){
            if(Session::get('vendor_name')){
                $whereWithVendor = "AND f.vend_num = '".Session::get('vendor_name')."'";
            }
            $this->crud->query = $this->crud->query->select('id', 'item')
            ->whereRaw("id = (SELECT MAX(f.id) FROM forecasts f WHERE f.item = forecasts.item AND SUBSTR(f.forecast_date, 1, 10) = SUBSTR(forecasts.forecast_date, 1, 10) {$whereWithVendor})")
            ->where(function($query) use($search){
                $query->whereRaw("qty = '{$search}'")
                ->orWhereRaw("item LIKE '%{$search}%'");
            })->groupBy('item');
        }else if($startForecast->type == 'month'){
            $months = [
                '01' => 'Jan',
                '02' => 'Feb',
                '03' => 'Mar',
                '04' => 'Apr',
                '05' => 'May',
                '06' => 'Jun',
                '07' => 'Jul',
                '08' => 'Aug',
                '09' => 'Sep',
                '10' => 'Oct',
                '11' => 'Nov',
                '12' => 'Dec'
            ];

            if(Session::get('vendor_name')){
                $whereWithVendor = "AND f.vend_num = '".Session::get('vendor_name')."'";
                $whereWithVendor_ = "AND f_.vend_num = '".Session::get('vendor_name')."'";
            }

            $this->crud->query = $this->crud->query->whereRaw("id = (SELECT MAX(f.id) FROM forecasts f WHERE f.item = forecasts.item AND SUBSTR(f.forecast_date, 1, 10) = SUBSTR(forecasts.forecast_date, 1, 10) {$whereWithVendor})");

            $selectRaw = ['item'];

            $columns = collect($columns);

            $i = 1;

            $columnMap = $columns->map(function($date) use($months){
                $exp = explode(' ', $date);
                $monthNumber = array_search($exp[0], $months);
                return $exp[1].'-'.$monthNumber;
            });

            foreach($columnMap->all() as $date){
                $raw = DB::raw("(SELECT SUM(f.qty) FROM forecasts f WHERE f.item = forecasts.item 
                AND SUBSTR(f.forecast_date, 1, 7) = '{$date}' 
                AND id = (SELECT MAX(f_.id) FROM forecasts f_ WHERE f_.item = f.item AND SUBSTR(f_.forecast_date, 1, 10) =
                          SUBSTR(f.forecast_date, 1, 10) {$whereWithVendor_})) as bulan{$i}");
                $selectRaw[] = $raw;
                $i++;
            }

            $this->crud->query = $this->crud->query
            ->select($selectRaw)
            ->havingRaw("item LIKE '%{$search}%'")
            ->orHavingRaw('bulan1 = ?', [$search])
            ->orHavingRaw('bulan2 = ?', [$search])
            ->orHavingRaw('bulan3 = ?', [$search])
            ->orHavingRaw('bulan4 = ?', [$search])
            ->orHavingRaw('bulan5 = ?', [$search])
            ->orHavingRaw('bulan6 = ?', [$search])
            ->orHavingRaw('bulan7 = ?', [$search])
            ->orHavingRaw('bulan8 = ?', [$search])
            ->orHavingRaw('bulan9 = ?', [$search])
            ->orHavingRaw('bulan10 = ?', [$search])
            ->orHavingRaw('bulan11 = ?', [$search])
            ->orHavingRaw('bulan12 = ?', [$search])
            ->orHavingRaw('bulan13 = ?', [$search])
            ->groupBy('item');
        }

        $this->crud->query = $this->crud->query->orderBy('id', 'DESC');
    }

    public function search(){
        $this->crud->hasAccessOrFail('list');
        // $this->crud->applyUnappliedFilters();
        $totalRows = $this->crud->query->get()->count(); # $this->crud->model->count();
        $filteredRows = $this->crud->query->toBase()->getCountForPagination(); # $this->crud->query->toBase()->getCountForPagination();
        $startIndex = request()->input('start') ?: 0;
        // if a search term was present
        if ((request()->input('search') && request()->input('search')['value']) && (request()->input('search')['value'] != '0')) {
            // filter the results accordingly
            // recalculate the number of filtered rows
            // $this->applySearchForecast();
            $search = request()->input('search')['value'];
            $this->crud->query = $this->crud->query->where("item", "LIKE", "%$search%");
            $filteredRows = $this->crud->query->toBase()->getCountForPagination();
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
            $forecast->type = 'month';

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

        $draw = 0;
        if(request()->input('draw')){
            $draw = (int) request()->input('draw');
        }

        $callback = array(
            'draw'=>$draw, // Ini dari datatablenya untuk tanda pada halaman pagination
            'recordsTotal' => $totalRows, // total dari semua row
            'recordsFiltered' => $filteredRows,
            'data' => $resultForecast,
        );
        return $callback;
    }

    function export2(){
        $this->setQuery();

        $entries = $this->crud->getEntries();

        $getItem = $entries->map(function($item){
            return $item->item;
        });

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
            $forecast->type = 'month';

        }

        # memulai forecast
        $start = $forecast->forecastStart();
        # menampilkan hasil forecast
        $resultForecast =  $start->getResultForecast();

        $columns = $start->getColumns();

        CRUD::addColumn([
            'label'     => 'Nama Item', // Table column heading
            'name'      => 'name_item',
        ]);
        foreach($columns as $key => $column){
            CRUD::addColumn([
                'label' => ($forecast->type == 'week') ? "{$column['export_value']}" : $column,
                'name' => "column_" . "{$key}",
                'rome_symbol' => ($forecast->type == 'week') ? $column['rome_symbol'] : '',
                'type' => 'forecast',
                // 'orderable' => false,
            ]);
        }

        // $this->crud->columnHeader = $start->columnHeader;

        // dd([
        //     'columnHeader' => $start->columnHeader,
        //     'column' => $start->getColumns(),
        //     'column_crud' => $this->crud->columns(), 
        //     'type' => $start->type,
        //     'result' => $resultForecast,
        // ]);


        $dateFrom = new DateTime($forecast->fromDate);
        $dateTarget = new DateTime($forecast->targetDate);


        $nameFileDownload = "Forecast_".$forecast->type." - ".$dateFrom->format('F')." ".$dateFrom->format('Y')." - ".$dateTarget->format('F')." ".$dateTarget->format('Y');

        return Excel::download(new ForecastExport(
            $this->crud->columns(), 
            $start->columnHeader, 
            $start->type, 
            $resultForecast), "{$nameFileDownload}.xlsx");
    }

    function getNameFromNumber($num) {
        $numeric = ($num - 1) % 26;
        $letter = chr(65 + $numeric);
        $num2 = intval(($num - 1) / 26);
        if ($num2 > 0) {
            return $this->getNameFromNumber($num2) . $letter;
        } else {
            return $letter;
        }
    }

    function export(){
        $this->setQuery();

        $entries = $this->crud->getEntries();

        $getItem = $entries->map(function($item){
            return $item->item;
        });

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
            $forecast->type = 'month';

        }

        # memulai forecast
        $start = $forecast->forecastStart();
        # menampilkan hasil forecast
        $resultForecast =  $start->getResultForecastExport();

        $columns = $start->getColumns();

        CRUD::addColumn([
            'label'     => 'Nama Item', // Table column heading
            'name'      => 'name_item',
        ]);
        foreach($columns as $key => $column){
            CRUD::addColumn([
                'label' => ($forecast->type == 'week') ? "{$column['export_value']}" : $column,
                'name' => "column_" . "{$key}",
                'rome_symbol' => ($forecast->type == 'week') ? $column['rome_symbol'] : '',
                'type' => 'forecast',
            ]);
        }

        $dateFrom = new DateTime($forecast->fromDate);
        $dateTarget = new DateTime($forecast->targetDate);


        $nameFileDownload = "Forecast_".$forecast->type." - ".$dateFrom->format('F')." ".$dateFrom->format('Y')." - ".$dateTarget->format('F')." ".$dateTarget->format('Y');

        $convertForecast = new ForecastExport(
            $columns, 
            $start->columnHeader, 
            $start->type, 
            $resultForecast);
        
        if($forecast->type == 'week'){
            return $convertForecast->exportForecastWeeks($nameFileDownload);
        }else if($forecast->type == 'days'){
            return $convertForecast->exportForecastDays($nameFileDownload);
        }
        return $convertForecast->exportForecastMonth($nameFileDownload);
    }
    


}
