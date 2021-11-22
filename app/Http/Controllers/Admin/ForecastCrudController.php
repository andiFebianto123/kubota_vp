<?php

namespace App\Http\Controllers\Admin;

use App\Http\Requests\ForecastRequest;
use Backpack\CRUD\app\Http\Controllers\CrudController;
use Backpack\CRUD\app\Library\CrudPanel\CrudPanelFacade as CRUD;
use Illuminate\Support\Facades\Session;
use App\Helpers\ForecastConverter;
use App\Models\PurchaseOrder;

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
        // $this->crud->query = $this->crud->query
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

        $ds = PurchaseOrder::join('vendors', 'vendors.id', '=', 'purchase_orders.vendor_id')
        ->select('purchase_orders.id as ID')
        ->whereNull('email_flag')->get();
        $a = [];
        foreach($ds as $dataBaru){
            echo "<pre>";
            print_r($dataBaru->ID);
            echo "</pre>";
        }
        die();
        dd($a);

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

        $this->crud->allowResponsive();

        $forecast = new forecastConverter;
        if(request("filter_forecast_by") != null){
            $forecast->type = (request("filter_forecast_by") == 'day') ? 'days' : 'week';
            Session::put("forecast_type", $forecast->type);
        }else{
            $forecast->type = 'days';
            Session::put("forecast_type", $forecast->type);
        }
        $start = $forecast->forecastStart();
        $columns = $start->getColumns();

        CRUD::addColumn([
            'label'     => 'Nama Item', // Table column heading
            'name'      => 'name_item',
        ]);
        foreach($columns as $column){
            CRUD::addColumn([
                'label' => "{$column}",
                'name' => "column_" . $column
            ]);
        }

        // if (request("filter_forecast_by") != null) {
        //     $ffb = request("filter_forecast_by");
        //     $this->dynamicColumns($ffb);
        // }else{
        //     $this->dynamicColumns('year');
        // }
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

    public function searchCustom()
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

        $dataCobaCoba = [
            [
                'Muhammad Andi febianto',
                23,
                ''
            ],
            [
                'Faiz Irfan Setiawan',
                17,
                ''
            ],
            [
                'Aditya Rafli',
                13,
                ''
            ],
        ];
        // $entries = $dataCobaCoba;
        // $startIndex => adalah offsetnya
        // $totalRows => adalah jumlah total baris semua datanya
        // dd([$totalRows, $filteredRows, $startIndex]);
        // $this->latihanCollect();
        $this->implementForecastConverter();

        $callback = array(
            'draw'=>request()->input('draw'), // Ini dari datatablenya untuk tanda pada halaman pagination
            'recordsTotal' => 3, // total dari semua row
            'recordsFiltered' => 3,
            'data' => $dataCobaCoba
        );
        // return $callback;
        // return $this->crud->getEntriesAsJsonForDatatables($entries, $totalRows, $filteredRows, $startIndex);
    }

    public function search(){
        $this->crud->hasAccessOrFail('list');
        // $this->crud->applyUnappliedFilters();
        $totalRows = 3;
        $filteredRows = 3;
        $startIndex = request()->input('start') ?: 0;
        // if a search term was present
        if (request()->input('search') && request()->input('search')['value']) {
            // filter the results accordingly
            // recalculate the number of filtered rows
            // $filteredRows = $this->crud->count();
        }
        // start the results according to the datatables pagination
        if (request()->input('start')) {
            // $this->crud->skip((int) request()->input('start'));
        }
        // limit the number of results according to the datatables pagination
        if (request()->input('length')) {
            // $this->crud->take((int) request()->input('length'));
        }


        $forecast = new forecastConverter;

        if(Session::get('forecast_type') == 'days'){
            $forecast->type = 'days';
        }else{
            $forecast->type = 'week';
        }

        $start = $forecast->forecastStart();

       //  $columns = $start->getColumns();

        // echo "<pre>";
        // print_r($start->dataDatePerWeek);
        // print_r($columns);
        // print_r($start->getResultForecast());
        // echo "</pre>";
        // die();
        // // dd($columns);

        $callback = array(
            'draw'=>request()->input('draw'), // Ini dari datatablenya untuk tanda pada halaman pagination
            'recordsTotal' => $totalRows, // total dari semua row
            'recordsFiltered' => $filteredRows,
            'data' => $start->getResultForecast()
        );
        // echo "<pre>";
        // print_r($start->getResultForecast());
        // echo "</pre>";
        return $callback;
    }

    function latihanCollect(){
        $contohData = [
			[
                'id' => 1,
				'name_item' => 'Item 1',
				'tgl_request_update' => '17-11-2021 01:00:00',
				'qty' => 12
			],
			[
                'id' => 10,
				'name_item' => 'Item 3',
				'tgl_request_update' => '17-11-2021 05:00:89',
				'qty' => 40
			],
			[
                'id' => 4,
				'name_item' => 'Item 2',
				'tgl_request_update' => '19-11-2021 03:00:00',
				'qty' => 10
			],
			[
                'id' => 5,
				'name_item' => 'Item 2',
				'tgl_request_update' => '20-12-2021 04:00:00',
				'qty' => 78
			],
			[
                'id' => 2,
				'name_item' => 'Item 1',
				'tgl_request_update' => '17-11-2021 01:03:00',
				'qty' => 5
			],
			[
                'id' => 3,
				'name_item' => 'Item 1',
				'tgl_request_update' => '07-12-2021 02:00:00',
				'qty' => 65
			],
		];
        // di bawah ini dipakai buat pencarian berdasarkan like '%$search%'
        $u = collect($contohData);
        $sorted = $u->sortBy([
            ['id', 'desc']
        ]);

        $filterU = $u->filter(function ($value){
            return false !== stristr($value['name_item'], 'Item 1');
        });
        // pencarian bersarkan tanggal
        $tgl = '17-11-2021';
        $filterSearch = $filterU->filter(function ($value) use($tgl){
            return false !== stristr($value['tgl_request_update'], $tgl);           
        });
        # ingin mencari data item dari tanggal sebelumnya 
        $searchBeforeDate = $filterU->filter(function ($value) use($tgl){
            return $value['tgl_request_update'] < $tgl;
        });
        // dd((int) $filterSearch->sortBy([
        //     ['id', 'desc']
        // ])->first()['qty']);
        dd($searchBeforeDate);

        dd($filterU->values()->all());
        dd($filterU->last());
        // $this->implementForecastConverter();
        die();
    }

    function implementForecastConverter(){
        $forecast = new forecastConverter;
        $forecast->type = 'days';
        $start = $forecast->forecastStart();
        echo "<pre>";
        print_r($start->getColumns());
        // print_r($start->dataDatePerWeek);
        print_r($start->getResultForecast());
        echo "</pre>";
        die();
    }

}
