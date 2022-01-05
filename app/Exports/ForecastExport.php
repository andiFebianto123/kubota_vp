<?php
namespace App\Exports;

use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;

class ForecastExport implements FromView
{
    private $column;
    private $columnHeader;
    private $type;
    private $resultForecast;

    function __construct($column, $columnHeader, $type, $resultForecast){
        $this->column = $column;
        $this->columnHeader = $columnHeader;
        $this->type = $type;
        $this->resultForecast = $resultForecast;
    }

    public function view(): View
    {
        return view('exports.excel.forecast', [
            'result' => $this->resultForecast,
            'crud' => [
                'columns' => $this->column,
                'columnHeader' => $this->columnHeader
            ],
            'type' => $this->type
        ]);
    }
}
