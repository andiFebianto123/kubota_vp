<?php

namespace App\Console\Commands;

use Illuminate\Support\Facades\DB;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;
use App\Mail\vendorNewPo;

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
        $pos = DB::table('po')->join('vendor', 'po.vend_num', '=', 'vendor.vend_num')
        ->select('po.id as ID','po.po_num as poNumber', 'vendor.vend_email as emails', 'vendor.buyer_email as buyers')
        ->whereNull('email_flag');
        if($pos->count() > 0){
            # alias terdapat data yang kosong
            $getPo = $pos->get();
            foreach($getPo as $po){
                $URL = env('APP_URL_PRODUCTION') . "/purchase-order/{$po->ID}/show";
                // $URL = url("/kubota_vp/kubota-vendor-portal/public/admin/purchase-order/{$po->ID}/show");
                $details = [
                    'po_num' => $po->poNumber,
                    'type' => 'reminder_po',
                    'title' => 'Ada PO baru',
                    'message' => 'Anda memiliki PO baru. Untuk melihat PO baru, anda dapat mengklik tombol dibawah ini.',
                    'url_button' => $URL.'?prev_session=true' //url("admin/purchase-order/{$po->ID}/show")
                ];
                if($po->emails != null){
                    $pecahEmailVendor = explode(';', $po->emails); // email nya vendor
                    $pecahEmailBuyer = ($po->buyers != null) ? explode(';', $po->buyers) : '';
                    Mail::to($pecahEmailVendor)
                    ->cc($pecahEmailBuyer)
                    ->send(new vendorNewPo($details));
                }
                
                $updatePo = DB::table('po')->where('id', $po->ID)->update([
                    'email_flag' => now()
                ]);
            }
        }
        $this->info("Cron is working fine!"); 
        // return Command::SUCCESS;
    }
}
