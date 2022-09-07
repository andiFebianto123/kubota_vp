<?php

namespace App\Models;

use Backpack\CRUD\app\Models\Traits\CrudTrait;
use Illuminate\Database\Eloquent\Model;
use Venturecraft\Revisionable\RevisionableTrait;

class TaxInvoice extends Model
{
    use CrudTrait;
    use RevisionableTrait;

    protected $table = 'delivery_status';
    protected $guarded = ['id'];
    protected $append = ['download_column'];

    public function getPaymentPlanDateAttribute($value)
    {
        return date('Y-m-d', strtotime($value));
    }

    
    public function getDownloadColumnAttribute()
    {
        $fakturPajak = '';
        $invoice = '';
        $suratJalan = '';

        if($this->file_faktur_pajak != null)
        { 
            $url = str_replace("files/","file-invoices/",asset($this->file_faktur_pajak));
            $fakturPajak = '<a class="btn btn-sm btn-link" target="_blank" href="'.$url.'" download><i class="la la-cloud-download-alt"></i> Faktur</a>';
        }
        if($this->invoice != null)
        { 
            $url = str_replace("files/","file-invoices/",asset($this->invoice));
            $invoice = '<a class="btn btn-sm btn-link" target="_blank" href="'.$url.'" download><i class="la la-cloud-download-alt"></i> Invoice</a>';
        }
        if($this->file_surat_jalan != null)
        { 
            $url = str_replace("files/","file-invoices/",asset($this->file_surat_jalan));
            $suratJalan = '<a class="btn btn-sm btn-link" target="_blank" href="'.$url.'" download><i class="la la-cloud-download-alt"></i> Surat Jalan</a>';
        }
        
        return '
            '.$fakturPajak.'
            '.$invoice.'
            '.$suratJalan.'
        ';
    }


    public function downloadOld(){
        return '
            <div class="dropdown show">
                <a class="btn btn-link dropdown-toggle" href="#" role="button" id="dropdownMenuLink" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                <i class="fa fa-ellipsis-v" aria-hidden="true"></i>
                </a>
            
                <div class="dropdown-menu" aria-labelledby="dropdownMenuLink">
                <a class="dropdown-item" href="#">Action</a>
                <a class="dropdown-item" href="#">Another action</a>
                <a class="dropdown-item" href="#">Something else here</a>
                </div>
            </div>
        ';        
    }

    public function excelExportAdvanceTop($crud = null){
        $url = url('export-tax-invoice');
        return '<a class="btn btn-sm btn-primary-vp" href="'.$url.'"><i class="la la-file-excel"></i> Export History Tax Payment</a>';
    }

    public function excelExportAdvanceBottom($crud = null){
        $url = url('export-tax-history-invoice');
        return '<a class="btn btn-sm btn-primary-vp" href="'.$url.'"><i class="la la-file-excel"></i> Export History Tax Payment</a>';
    }

}
