<?php

namespace App\Console\Commands;

use Illuminate\Support\Facades\DB;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;
use App\Mail\vendorNewPo;
use App\Mail\vendorRevisionPo;
use App\Models\PurchaseOrder;
use App\Models\PurchaseOrderLine;

class SendMailRevisionPoRealTime extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'vendor:realtime-revision-po';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Mengirim email ke vendor bila ada PO revisi';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */

    public function handle()
    {
        $maxBatch = PurchaseOrder::max('session_batch_process_revision');
        $batchSession = 1;
        $sessionIncrement = 0;
        if($maxBatch != null || $maxBatch > 0){
            $sessionIncrement = $maxBatch;
            $batchSession = $sessionIncrement + 1;
        }

        $pos = PurchaseOrder::join('vendor', 'po.vend_num', '=', 'vendor.vend_num')
        ->select('po.id as ID','po.po_num as poNumber','po.last_po_change_email','session_batch_process_revision',
             'po.po_change', 'vendor.vend_email as emails', 'vendor.buyer_email as buyers')
        ->whereColumn('last_po_change_email', '<','po_change')
        ->where(function($query) use ($batchSession){
            return $query->where('session_batch_process_revision', '<', $batchSession)
            ->orWhereNull('session_batch_process_revision');
        });

        if($pos->count() > 0){
            $getPo = $pos->take(3)->get();

            foreach($getPo as $poo){
                $updatePo = PurchaseOrder::where('id', $poo->ID)->first();
                $updatePo->session_batch_process_revision = $batchSession;
                $updatePo->save();
            }

            foreach($getPo as $po){

                $URL = env('APP_URL_PRODUCTION') . "/purchase-order/{$po->ID}/show";
                // $URL = url("/kubota_vp/kubota-vendor-portal/public/admin/purchase-order/{$po->ID}/show");
                $details = [
                    'po_num' => $po->poNumber,
                    'type' => 'revision_po',
                    'title' => 'Revisi PO ' . $po->poNumber. ' Rev.'.$po->po_change,
                    'message' => 'Anda memiliki PO yang direvisi. Untuk melihat PO tersebut, anda dapat mengklik tombol dibawah ini.',
                    'url_button' => $URL.'?prev_session=true' //url("admin/purchase-order/{$po->ID}/show")
                ];

                if($po->emails != null && ($po->last_po_change_email < $po->po_change)){
                    $vendEmails = str_replace(" ", "",str_replace(",", ";", $po->emails));
                    $buyerEmails = "";
                    if ($buyerEmails != null) {
                        $buyerEmails = str_replace(" ", "",str_replace(",", ";", $po->buyers));
                    }
                    $pecahEmailVendor = explode(';', $vendEmails);
                    $pecahEmailBuyer = explode(';', $buyerEmails);
                    
                    Mail::to($pecahEmailVendor)
                    ->cc($pecahEmailBuyer)
                    ->send(new vendorRevisionPo($details));

                    $thePo = PurchaseOrder::where('id', $po->ID)->first();
                    $thePo->last_po_change_email = $po->po_change;
                    $thePo->save();

                    $this->info("Sent ".$po->poNumber."::".$po->emails); 
                }
            }
        }
    }
}
