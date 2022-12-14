<?php

namespace App\Console\Commands;

use Exception;
use App\Helpers\Constant;
use App\Mail\VendorNewPo;
use App\Models\PurchaseOrder;
use App\Helpers\EmailLogWriter;
use App\Models\LogBatchProcess;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;

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
        DB::beginTransaction();
        $maxBatch = PurchaseOrder::max('session_batch_process');
        $batchSession = 1;
        $sessionIncrement = 0;
        if ($maxBatch != null || $maxBatch > 0) {
            $sessionIncrement = $maxBatch;
            $batchSession = $sessionIncrement + 1;
        }
        PurchaseOrder::join('vendor', 'po.vend_num', '=', 'vendor.vend_num')
            ->whereNull('email_flag')
            ->whereNull('last_po_change_email')
            ->whereNull('session_batch_process')
            ->whereExists(function ($query) {
                $query->from('po_line')->whereRaw('po_line.po_num = po.po_num')
                    ->where('po_line.status', 'O');
            })
            ->update(['session_batch_process' => $batchSession]);
        DB::commit();

        $pos = PurchaseOrder::join('vendor', 'po.vend_num', '=', 'vendor.vend_num')
            ->select('po.id as ID', 'po.po_num as poNumber', 'po.po_change', 'vendor.vend_email as emails', 'vendor.buyer_email as buyers')
            ->whereNull('email_flag')
            ->whereNull('last_po_change_email')
            ->where('session_batch_process', $batchSession)
            ->whereExists(function ($query) {
                $query->from('po_line')->whereRaw('po_line.po_num = po.po_num')
                    ->where('po_line.status', 'O');
            })
            ->get();

        foreach ($pos as $po) {
            // $existOrderedPoLine = PurchaseOrderLine::where('po_num', $po->poNumber)
            //     ->where('status', 'O')
            //     ->exists();

            // $countLogError = LogBatchProcess::where('po_num', $po->poNumber)
            //     ->where('type', 'New PO')
            //     ->count();
            $URL = env('APP_URL_PRODUCTION') . "/purchase-order/{$po->ID}/show";
            $thePo = PurchaseOrder::where('id', $po->ID)->first();
            $pecahEmailVendor = (new Constant())->emailHandler($po->emails, 'array');
            $pecahEmailBuyer = (new Constant())->emailHandler($po->buyers, 'array');
            try {
                // if ($existOrderedPoLine  && $countLogError < 11) {
                $details = [
                    'buyer_email' => $pecahEmailBuyer,
                    'po_num' => $po->poNumber,
                    'type' => 'reminder_po',
                    'title' => 'Ada PO ' . $po->poNumber . ' baru',
                    'message' => 'Anda memiliki PO baru. Untuk melihat PO baru, anda dapat mengklik tombol dibawah ini.',
                    'url_button' => $URL . '?prev_session=true', //url("admin/purchase-order/{$po->ID}/show")
                ];
    
                Mail::to($pecahEmailVendor)
                    ->cc($pecahEmailBuyer)
                    ->send(new VendorNewPo($details));
                $thePo->last_po_change_email = $po->po_change;
                $thePo->email_flag = now();
                $thePo->save();

                $this->info("Sent " . $po->poNumber . "::" . $po->emails);
            } catch (Exception $e) {
                (new EmailLogWriter)->create(
                    'New Purchase Order - [' . $po->poNumber . ']',
                    json_encode($pecahEmailVendor),
                    $e->getMessage(),
                    json_encode($pecahEmailBuyer),
                    env('MAIL_PO_BCC',""),
                    json_encode($pecahEmailBuyer)
                );
            }
            // }
        }
    }
}
