<?php
namespace App\Library;

use Box\Spout\Writer\Common\Creator\WriterEntityFactory;
use Box\Spout\Common\Entity\Row;

// style for spot excel

use Box\Spout\Writer\Common\Creator\Style\StyleBuilder;
use Box\Spout\Common\Entity\Style\CellAlignment;
use Box\Spout\Common\Entity\Style\Color;

class ExportXlsx {

    protected $writer;

    function __construct($filename = 'default.xlsx'){
        $this->writer = WriterEntityFactory::createXLSXWriter();
        $this->writer->setTempFolder(storage_path('framework/laravel-excel'));
        $this->writer->openToBrowser($filename); // stream data directly to the browser
    }

    public function addRow(Array $value,  $style = null){
        $defaultStyleForRow = (new StyleBuilder())
                                ->setFontBold()
                                ->setFontColor(Color::BLACK)
                                ->setCellAlignment(CellAlignment::RIGHT)
                                ->build();
                                // ->setBackgroundColor(Color::rgb(102, 171, 163))

        if($style == null){
            $rowFromValues = WriterEntityFactory::createRowFromArray($value, $defaultStyleForRow);
        }else{
            $rowFromValues = WriterEntityFactory::createRowFromArray($value, $style);
        }

        $this->writer->addRow($rowFromValues);
    }

    public function currentSheet(){
        return $this->writer->getCurrentSheet();
    }

    public function close(){
        $this->writer->close();
        if(isset($GLOBALS['col'])){
            unset($GLOBALS['col']);
        }
    }
}

?>