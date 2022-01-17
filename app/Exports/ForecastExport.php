<?php
namespace App\Exports;

use DateTime;
use App\Helpers\Constant;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Session;
use Maatwebsite\Excel\Concerns\FromView;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class ForecastExport implements FromView
{
    private $column;
    private $columnHeader;
    private $type;
    private $resultForecast;

    const STYLE_NAME_ITEM = [
        'font' => [
            'bold' => true,
        ],
        'alignment' => [
            'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER,
            'vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER,
        ],
        'borders' => [
            'top' => [
                'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
            ],
            'left' => [
                'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
            ],
            'right' => [
                'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
            ],
            'bottom' => [
                'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
            ],
        ]
    ];
    const STYLE_COLUMN_HEADER = [
        'font' => [
            'bold' => true,
        ],
        'alignment' => [
            'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER,
            'vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER,
        ],
        'borders' => [
            'left' => [
                'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
            ],
            'right' => [
                'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
            ],
            'bottom' => [
                'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
            ],
        ]
    ];

    const STYLE_RESULT = [
        'borders' => [
            'left' => [
                'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
            ],
            'right' => [
                'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
            ],
            'bottom' => [
                'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
            ],
        ]
    ];

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

    public function exportForecastWeeks($nameFileDownload = 'Default'){
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        // create header

        if(Session::get('vendor_name')){
            $vendor = Session::get('vendor_name');
            $sheet->setCellValue("A2", "Nama Vendor");
            $sheet->mergeCells("B2:C2");
            $sheet->setCellValue("B2", ": $vendor");

            // change 
            $baris = 3;
            $sheet->mergeCells("A3:A4");
            $sheet->getStyle("A3:A4")->applyFromArray(self::STYLE_NAME_ITEM);
            // change
        }else{
            // change 
            $baris = 1;
            $sheet->mergeCells("A1:A2");
            $sheet->getStyle("A1:A2")->applyFromArray(self::STYLE_NAME_ITEM);
            // change
        }
        

        $sheet->setCellValueByColumnAndRow(1, $baris, 'Nama Item');
        $sheet->getColumnDimension('A')->setWidth(210, 'px');

        $startAlphabetNum = 2;
        $endAlphabetNum = 5;
        foreach ($this->columnHeader as $value) {
            $mergeStartCoordinate = Constant::getNameFromNumber($startAlphabetNum);
            $mergeEndCoordinate = Constant::getNameFromNumber($endAlphabetNum);

            $sheet->getStyle($mergeStartCoordinate.''.$baris.':'.$mergeEndCoordinate.''.$baris)->applyFromArray(self::STYLE_NAME_ITEM);
            $sheet->mergeCells($mergeStartCoordinate.''.$baris.':'.$mergeEndCoordinate.''.$baris);
            $sheet->setCellValueByColumnAndRow($startAlphabetNum, $baris, $value);

            $startAlphabetNum = $endAlphabetNum + 1;
            $endAlphabetNum = $endAlphabetNum + 4;
        }

        $col = 2;
        $baris = $baris + 1;
        foreach($this->column as $column){
            $dimensionColumn = Constant::getNameFromNumber($col);
            $sheet->getStyle($dimensionColumn.''.$baris)->applyFromArray(self::STYLE_COLUMN_HEADER);
            $sheet->getColumnDimension($dimensionColumn)->setWidth(78, 'px');
            $first = substr($column['first_date'], 8, 2);
            $last = substr($column['last_date'], 8, 2);
            $sheet->setCellValueByColumnAndRow($col, $baris, "{$first}  s.d.  {$last}");
            $col++;
        }

        $baris = $baris + 1;
        $col = 1;
        foreach($this->resultForecast as $getForecast){
            foreach($getForecast as $result){
                $dimensionColumn = Constant::getNameFromNumber($col);
                $sheet->setCellValue($dimensionColumn.''.$baris, $result);
                $sheet->getStyle($dimensionColumn.''.$baris)->applyFromArray(self::STYLE_RESULT);
                $col++;
            }
            $col = 1;
            $baris++;
        }
    
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment; filename="'.$nameFileDownload.'.xlsx"'); // Set nama file excel nya
        header('Cache-Control: max-age=0');
        $writer = new Xlsx($spreadsheet);
        $writer->save('php://output');
    }

    function exportForecastDays($nameFileDownload = 'Default'){
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
         // create header

         if(Session::get('vendor_name')){
            $vendor = Session::get('vendor_name');
            $sheet->setCellValue("A2", "Nama Vendor");
            $sheet->mergeCells("B2:C2");
            $sheet->setCellValue("B2", ": $vendor");
            // change 
            $baris = 3;
            $sheet->mergeCells("A3:A4");
            $sheet->getStyle("A3:A4")->applyFromArray(self::STYLE_NAME_ITEM);
            // change
        }else{
            // change 
            $baris = 1;
            $sheet->mergeCells("A1:A2");
            $sheet->getStyle("A1:A2")->applyFromArray(self::STYLE_NAME_ITEM);
            // change
        }

        $sheet->setCellValueByColumnAndRow(1, $baris, 'Nama Item');
        $sheet->getColumnDimension('A')->setWidth(210, 'px');

        $startAlphabetNum = 2;
        $endAlphabetNum = 0;
        foreach ($this->columnHeader as $value) {

            $key = $value['key'].'-01';
            $newDate = new DateTime($key);
            $key = $newDate->format('F Y');

            $colspan = count($value['data']) - 1;
            $endAlphabetNum = $startAlphabetNum + $colspan;

            $mergeStartCoordinate = Constant::getNameFromNumber($startAlphabetNum);
            $mergeEndCoordinate = Constant::getNameFromNumber($endAlphabetNum);

            $sheet->getStyle($mergeStartCoordinate.''.$baris.':'.$mergeEndCoordinate.''.$baris)->applyFromArray(self::STYLE_NAME_ITEM);
            $sheet->mergeCells($mergeStartCoordinate.''.$baris.':'.$mergeEndCoordinate.''.$baris);
            $sheet->setCellValueByColumnAndRow($startAlphabetNum, $baris, $key);

            $startAlphabetNum = $endAlphabetNum + 1;
        }

        $col = 2;
        $baris = $baris + 1;

        foreach($this->column as $column){
            $dimensionColumn = Constant::getNameFromNumber($col);
            $sheet->getStyle($dimensionColumn.''.$baris)->applyFromArray(self::STYLE_COLUMN_HEADER);
            $sheet->getColumnDimension($dimensionColumn)->setWidth(78, 'px');
            $columnValue = substr($column, 8, 2);
            $sheet->setCellValueByColumnAndRow($col, $baris, $columnValue);
            $col++;
        }

        $baris = $baris + 1;
        $col = 1;
        foreach($this->resultForecast as $getForecast){
            foreach($getForecast as $result){
                $dimensionColumn = Constant::getNameFromNumber($col);
                $sheet->setCellValue($dimensionColumn.''.$baris, $result);
                $sheet->getStyle($dimensionColumn.''.$baris)->applyFromArray(self::STYLE_RESULT);
                $col++;
            }
            $col = 1;
            $baris++;
        }

        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment; filename="'.$nameFileDownload.'.xlsx"'); // Set nama file excel nya
        header('Cache-Control: max-age=0');
        $writer = new Xlsx($spreadsheet);
        $writer->save('php://output');

    }

    function exportForecastMonth($nameFileDownload = 'default'){
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        if(Session::get('vendor_name')){
            $vendor = Session::get('vendor_name');
            $sheet->setCellValue("A2", "Nama Vendor");
            $sheet->mergeCells("B2:C2");
            $sheet->setCellValue("B2", ": $vendor");
            // change 
            $baris = 3;
            $sheet->getStyle("A3")->applyFromArray(self::STYLE_NAME_ITEM);
            // change
        }else{
            $baris = 1;
            $sheet->getStyle("A1")->applyFromArray(self::STYLE_NAME_ITEM);
        }
        
        $sheet->setCellValueByColumnAndRow(1, $baris, 'Nama Item');
        $sheet->getColumnDimension('A')->setWidth(210, 'px');

        $col = 2;

        foreach($this->column as $column){
            $styleColumnHeader = [
                'font' => [
                    'bold' => true,
                ],
                'alignment' => [
                    'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER,
                    'vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER,
                ],
                'borders' => [
                    'top' => [
                        'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                    ],
                    'left' => [
                        'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                    ],
                    'right' => [
                        'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                    ],
                    'bottom' => [
                        'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                    ],
                ]
            ];
            $dimensionColumn = Constant::getNameFromNumber($col);
            $sheet->getStyle($dimensionColumn.''.$baris)->applyFromArray($styleColumnHeader);
            $sheet->getColumnDimension($dimensionColumn)->setWidth(100, 'px');
            $sheet->setCellValueByColumnAndRow($col, $baris, $column);
            $col++;
        }

        $baris++;
        $col = 1;
        foreach($this->resultForecast as $getForecast){
            foreach($getForecast as $result){
                $dimensionColumn = Constant::getNameFromNumber($col);
                $sheet->setCellValue($dimensionColumn.''.$baris, $result);
                $sheet->getStyle($dimensionColumn.''.$baris)->applyFromArray(self::STYLE_RESULT);
                $col++;
            }
            $col = 1;
            $baris++;
        }

        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment; filename="'.$nameFileDownload.'.xlsx"'); // Set nama file excel nya
        header('Cache-Control: max-age=0');
        $writer = new Xlsx($spreadsheet);
        $writer->save('php://output');
    }

}
