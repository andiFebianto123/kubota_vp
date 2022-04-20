<?php

namespace App\Console\Commands;

use Illuminate\Support\Facades\DB;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;
use App\Mail\vendorNewPo;
use App\Models\PurchaseOrder;
use App\Models\PurchaseOrderLine;

class SendMailVendorRealTime extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'vendor:realtime';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Mengirim email ke vendor bila ada PO baru';

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
        $pos = PurchaseOrder::join('vendor', 'po.vend_num', '=', 'vendor.vend_num')
        ->select('po.id as ID','po.po_num as poNumber', 'vendor.vend_email as emails', 'vendor.buyer_email as buyers')
        ->whereNull('email_flag')
        ->where(function($query){
            return $query->where('session_batch_process', 0)
            ->orWhereNull('session_batch_process');
        });

        if($pos->count() > 0){
            # alias terdapat data yang kosong
            $sessionIncrement = PurchaseOrder::max('session_batch_process');
            $batchSession = 1;
            if($sessionIncrement != null){
                $batchSession = $sessionIncrement + 1;
            }
            $getPo = $pos->get();



            foreach($getPo as $poo){
                $updatePo = PurchaseOrder::where('id', $poo->ID)->first();
                $updatePo->session_batch_process = $batchSession;
                $updatePo->save();
            }

            foreach($getPo as $po){
                $existOrderedPoLine = PurchaseOrderLine::where('po_num', $po->poNumber)
                        ->where('status', 'O')
                        ->exists();

                $URL = env('APP_URL_PRODUCTION') . "/purchase-order/{$po->ID}/show";
                // $URL = url("/kubota_vp/kubota-vendor-portal/public/admin/purchase-order/{$po->ID}/show");
                $details = [
                    'po_num' => $po->poNumber,
                    'type' => 'reminder_po',
                    'title' => 'Ada PO ' . $po->poNumber . ' baru',
                    'message' => 'Anda memiliki PO baru. Untuk melihat PO baru, anda dapat mengklik tombol dibawah ini.',
                    'url_button' => $URL.'?prev_session=true' //url("admin/purchase-order/{$po->ID}/show")
                ];

                $thePo = PurchaseOrder::where('id', $po->ID)->first();

                // if($thePo->email_flag != null){
                //     continue;
                // }

                if($po->emails != null && $existOrderedPoLine){
                    $pecahEmailVendor = explode(';', $po->emails); // email nya vendor
                    $pecahEmailBuyer = ($po->buyers != null) ? explode(';', $po->buyers) : '';
                    Mail::to($pecahEmailVendor)
                    ->cc($pecahEmailBuyer)
                    ->send(new vendorNewPo($details));
                }

                $thePo->email_flag = now();
                // $thePo->session_batch_proccess = $batchSession;
                $thePo->save();

            }
        }
        $this->info("Cron is working fine!"); 
        // return Command::SUCCESS;
    }
}
