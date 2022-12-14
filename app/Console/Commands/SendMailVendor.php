<?php

namespace App\Console\Commands;

use App\Helpers\Constant;
use Illuminate\Support\Facades\DB;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;
use App\Mail\VendorNewPo;
use App\Models\PurchaseOrder;

class SendMailVendor extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'vendor:daily';

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
        ->select('po.id as ID','po.po_num as poNumber', 'vendor.vend_email as emails', 'vendor.buyer_email as buyers', 'po.session_batch_process')
        ->whereNull('email_flag');
        if($pos->count() > 0){
            # alias terdapat data yang kosong
            $getPo = $pos->get();
            $sessionIncrement = PurchaseOrder::max('session_batch_process');
            $batchSession = 1;
            if($sessionIncrement != null){
                $batchSession = $sessionIncrement + 1;
            }

            foreach($getPo as $poo){
                $updatePo = PurchaseOrder::where('id', $poo->ID)->first();
                if($updatePo->session_batch_process == null){
                    $updatePo->session_batch_process = $batchSession;
                    $updatePo->save();
                }
            }


            foreach($getPo as $po){
                
                $URL = env('APP_URL_PRODUCTION') . "/purchase-order/{$po->ID}/show";                

                $thePo = PurchaseOrder::where('id', $po->ID)->first();
                if($thePo->email_flag != null){
                    continue;
                }

                if($po->emails != null){
                    $pecahEmailVendor = (new Constant())->emailHandler($po->emails, 'array');
                    $pecahEmailBuyer = (new Constant())->emailHandler($po->buyers, 'array');
                    $details = [
                        'buyer_email' => $pecahEmailBuyer,
                        'po_num' => $po->poNumber,
                        'type' => 'reminder_po',
                        'title' => 'Ada PO ' . $po->poNumber . ' baru',
                        'message' => 'Anda memiliki PO baru. Untuk melihat PO baru, anda dapat mengklik tombol dibawah ini.',
                        'url_button' => $URL.'?prev_session=true' //url("admin/purchase-order/{$po->ID}/show")
                    ];
                    Mail::to($pecahEmailVendor)
                    ->cc($pecahEmailBuyer)
                    ->send(new VendorNewPo($details));
                }
                $thePo->email_flag = now();
                $thePo->save();
            }
        }
        $this->info("Cron is working fine!"); 
        // return Command::SUCCESS;
    }


}
