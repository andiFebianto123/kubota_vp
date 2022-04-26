<?php

namespace App\Console\Commands;

use App\Helpers\Constant;
use Illuminate\Support\Facades\DB;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;
use App\Mail\VendorRevisionPo;
use App\Models\LogBatchProcess;
use App\Models\PurchaseOrder;
use App\Models\PurchaseOrderLine;
use Exception;

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
        
        DB::beginTransaction();
        $maxBatch = PurchaseOrder::max('session_batch_process');
        $batchSession = 1;
        $sessionIncrement = 0;
        if ($maxBatch != null || $maxBatch > 0) {
            $sessionIncrement = $maxBatch;
            $batchSession = $sessionIncrement + 1;
        }
        PurchaseOrder::join('vendor', 'po.vend_num', '=', 'vendor.vend_num')
            ->whereColumn('last_po_change_email', '<', 'po_change')
            ->whereNull('email_flag')
            ->whereNull('session_batch_process')
            ->update(['session_batch_process' => $batchSession]);
        DB::commit();

        $pos = PurchaseOrder::join('vendor', 'po.vend_num', '=', 'vendor.vend_num')
            ->select(
                'po.id as ID',
                'po.po_num as poNumber',
                'po.last_po_change_email',
                'session_batch_process',
                'po.po_change',
                'vendor.vend_email as emails',
                'vendor.buyer_email as buyers'
            )
            ->whereColumn('last_po_change_email', '<', 'po_change')
            ->whereNull('email_flag')
            ->where('session_batch_process', $batchSession)
            ->get();

        foreach ($pos as $po) {
            $countLogError = LogBatchProcess::where('po_num', $po->poNumber)
                ->where('type', 'Revision PO')
                ->count();

            $URL = env('APP_URL_PRODUCTION') . "/purchase-order/{$po->ID}/show";

            if ($po->emails != null && ($po->last_po_change_email < $po->po_change) && $countLogError < 11) {
                $pecahEmailVendor = (new Constant())->emailHandler($po->emails, 'array');
                $pecahEmailBuyer = (new Constant())->emailHandler($po->buyers, 'array');
                $details = [
                    'buyer_email' => $pecahEmailBuyer,
                    'po_num' => $po->poNumber,
                    'type' => 'revision_po',
                    'title' => 'Revisi PO ' . $po->poNumber . ' Rev.' . $po->po_change,
                    'message' => 'Anda memiliki PO yang direvisi. Untuk melihat PO tersebut, anda dapat mengklik tombol dibawah ini.',
                    'url_button' => $URL . '?prev_session=true' //url("admin/purchase-order/{$po->ID}/show")
                ];

                try {
                    // Mail::to($pecahEmailVendor)
                    //     ->cc($pecahEmailBuyer)
                    //     ->send(new VendorRevisionPo($details));

                    $thePo = PurchaseOrder::where('id', $po->ID)->first();
                    $thePo->last_po_change_email = $po->po_change;
                    $thePo->save();

                    $this->info("Sent " . $po->poNumber . "::" . $po->emails);
                } catch (Exception $e) {
                    LogBatchProcess::create([
                        'mail_to' => json_encode($pecahEmailVendor),
                        'mail_cc' => json_encode($pecahEmailBuyer),
                        'mail_reply_to' => json_encode($pecahEmailBuyer),
                        'po_num' => $po->poNumber,
                        'error_message' => $e->getMessage(),
                        'type' => 'Revision PO',
                    ]);
                    return Command::FAILURE;
                }
            }
        }
    }
}
