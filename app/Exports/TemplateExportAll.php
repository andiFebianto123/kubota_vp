<?php
namespace App\Exports;

use App\Helpers\Constant;
use App\Helpers\DsValidation;
use App\Models\PurchaseOrder;
use App\Models\PurchaseOrderLine;
use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Style\Protection;
use Maatwebsite\Excel\Concerns\WithColumnFormatting;
use PhpOffice\PhpSpreadsheet\Cell\Cell;
use Maatwebsite\Excel\Concerns\WithCustomValueBinder;
use PhpOffice\PhpSpreadsheet\Cell\DefaultValueBinder;
use PhpOffice\PhpSpreadsheet\Cell\DataType;


class TemplateExportAll extends DefaultValueBinder implements FromView, WithCustomValueBinder, WithEvents
{
    public $data;
    public $header;
    public $resultCallback;
    public $styleHeader;
    public $title;

    private $data_function = [];

    public function __construct(
        $data, 
        $header, 
        $resultCallback, 
        $styleHeader, 
        $title = "Sheet 1",
        )
    {
        $this->title = $title;
        $this->data = $data;
        $this->header = $header;
        $this->resultCallback = $resultCallback;
        $this->styleHeader = $styleHeader;
    }

    public function addFunctionChangeFormatHeader($func){
            $data_function[] = $func;
    }


    public function view(): View
    {
        $data['title'] = $this->title;
        $data['datas'] = $this->data;
        $data['headers'] = $this->header;
        $data['resultValue'] = $this->resultCallback;
        
        return view('exports.excel.template_all', $data);
    }

    
    public function registerEvents(): array
    {
        return [
            AfterSheet::class => $this->styleHeader,
        ];
    }

    function changeFormat(Cell $cell, $value){

        if($this->title == 'Report Delivery Sheet'){
            if ($cell->getColumn() == 'H') {
                $cell->setValueExplicit($value, DataType::TYPE_STRING);
                return true;
            }
        }

        if($this->title == 'Report Delivery Status'){
            if ($cell->getColumn() == 'H') {
                $cell->setValueExplicit($value, DataType::TYPE_STRING);
                return true;
            }
            if ($cell->getColumn() == 'AA') {
                $cell->setValueExplicit($value, DataType::TYPE_STRING);
                return true;
            }
        }
    }

    public function bindValue(Cell $cell, $value)
    {
        $d = $this->changeFormat($cell, $value);
        if($d === true){
            return true;
        }
        // else return default behavior
        return parent::bindValue($cell, $value);
    }

}