<?php

namespace App\Http\Controllers\Admin;

use App\Http\Requests\ForecastRequest;
use Backpack\CRUD\app\Http\Controllers\CrudController;
use Backpack\CRUD\app\Library\CrudPanel\CrudPanelFacade as CRUD;
use Illuminate\Support\Facades\Session;

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

        // $arr_week = ["Week 1","Week 2", "Week 3", "Week 4"];
        // $arr_day = ["Senin","Selasa", "Rabu", "Kamis", "Jumat", "Sabtu", "Minggu"];
        // $ar_month = ["Januari","Februari", "Maret", "April", "Mei", "Juni", "Juli", "Agustus", "September", "Oktober", "November", "Desember"];
        CRUD::addColumn([
            'label'     => 'Forecast Number', // Table column heading
            'name'      => 'forecast_num',
        ]);

        // $list=array();
        // $month = 4;
        // $year = 2020;

        // for($d=1; $d<=31; $d++)
        // {
        //     $time=mktime(12, 0, 0, $month, $d, $year);          
        //     if (date('m', $time)==$month)       
        //         $list[]=date('Y-m-d-D', $time);
        // }

        // dd($list);

        if (request("filter_forecast_by") != null) {
            $ffb = request("filter_forecast_by");
            $this->dynamicColumns($ffb);
        }else{
            $this->dynamicColumns('year');
        }
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

        $columns = $arr_filters[$ffb];
        $index_filter = array_search($ffb, $arr_filter_forecasts);
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

            CRUD::addColumn($arr_dynamic_col);
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
}
