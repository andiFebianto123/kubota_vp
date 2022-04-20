<?php

namespace App\Console\Commands;

use Illuminate\Support\Facades\DB;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;
use App\Mail\vendorNewPo;
use App\Models\PurchaseOrder;
use App\Models\PurchaseOrderLine;

class TestSendMailRevisionPoRealTime extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'vendor:test-realtime-revision-po';

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
        $URL = env('APP_URL_PRODUCTION') . "/purchase-order/111/show";

        $details = [
            'po_num' => 'dadad',
            'type' => 'revision_po',
            'title' => 'Revisi PO ada',
            'message' => 'Anda memiliki PO yang direvisi. Untuk melihat PO tersebut, anda dapat mengklik tombol dibawah ini.',
            'url_button' => $URL.'?prev_session=true' //url("admin/purchase-order/{$po->ID}/show")
        ];

            $vendEmails = str_replace(",", ";", "abc@gmail.com,bbb@gmail.com,ccc@gmail.com");
            $buyerEmails = str_replace(",", ";", "qabc@gmail.com,qbbb@gmail.com,qccc@gmail.com");
            $pecahEmailVendor = explode(';', $vendEmails);
            $pecahEmailBuyer = ($buyerEmails != null) ? explode(';', $buyerEmails) : '';
            Mail::to($pecahEmailVendor)
            ->cc($pecahEmailBuyer)
            ->send(new vendorNewPo($details));

        $this->info("Cron is working fine!"); 
    }
}
