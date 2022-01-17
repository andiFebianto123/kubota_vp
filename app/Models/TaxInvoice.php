<?php

namespace App\Models;

use Backpack\CRUD\app\Models\Traits\CrudTrait;
use Illuminate\Database\Eloquent\Model;
use Venturecraft\Revisionable\RevisionableTrait;

class TaxInvoice extends Model
{
    use CrudTrait;
    use RevisionableTrait;

    /*
    |--------------------------------------------------------------------------
    | GLOBAL VARIABLES
    |--------------------------------------------------------------------------
    */

    protected $table = 'delivery_status';
    // protected $primaryKey = 'id';
    // public $timestamps = false;
    protected $guarded = ['id'];
    // protected $fillable = [];
    // protected $hidden = [];
    // protected $dates = [];

    /*
    |--------------------------------------------------------------------------
    | FUNCTIONS
    |--------------------------------------------------------------------------
    */

    /*
    |--------------------------------------------------------------------------
    | RELATIONS
    |--------------------------------------------------------------------------
    */

    /*
    |--------------------------------------------------------------------------
    | SCOPES
    |--------------------------------------------------------------------------
    */

    /*
    |--------------------------------------------------------------------------
    | ACCESSORS
    |--------------------------------------------------------------------------
    */

    /*
    |--------------------------------------------------------------------------
    | MUTATORS
    |--------------------------------------------------------------------------
    */
    public function download()
    {
        $faktur_pajak = ($this->file_faktur_pajak != null) ? '<a class="btn btn-sm btn-link" target="_blank" href="'.$this->file_faktur_pajak.'" download><i class="la la-cloud-download-alt"></i> Download Faktur</a>' : '';
        $invoice = ($this->invoice != null) ? '<a class="btn btn-sm btn-link" target="_blank" href="'.$this->invoice.'" download><i class="la la-cloud-download-alt"></i> Download Invoice</a>' : '';
        $surat_jalan = ($this->file_surat_jalan != null) ? '<a class="btn btn-sm btn-link" target="_blank" href="'.$this->file_surat_jalan.'" download><i class="la la-cloud-download-alt"></i> Download Surat Jalan</a>
        ' : '';
        return '
            '.$faktur_pajak.'
            '.$invoice.'
            '.$surat_jalan.'
        ';
    }

    public function downloadV2(){
        // .datatable table td {
        //     overflow: visible;
        // }
        return '
            <div class="dropdown show">
                <a class="btn btn-secondary dropdown-toggle" href="#" role="button" id="dropdownMenuLink" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                Dropdown link
                </a>
            
                <div class="dropdown-menu" aria-labelledby="dropdownMenuLink">
                <a class="dropdown-item" href="#">Action</a>
                <a class="dropdown-item" href="#">Another action</a>
                <a class="dropdown-item" href="#">Something else here</a>
                </div>
            </div>
        ';
                
    }
}
