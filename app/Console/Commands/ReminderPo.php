<?php

namespace App\Console\Commands;

use App\Helpers\Constant;
use App\Helpers\EmailLogWriter;
use App\Mail\ReminderAcceptPo;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use App\Mail\vendorNewPo;
use Exception;
use Log;

class ReminderPo extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'reminder:po_line';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Melakukan cek reminder PO dan kirim email';

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
        $config = \App\Models\Configuration::all();
        $dl = collect($config);
        $reminderDay = $dl->where('name', 'email_reminder_day');

        $dataPo = \App\Models\PurchaseOrderLine::join('po', 'po.po_num' , '=', 'po_line.po_num')
        ->join('vendor', 'po.vend_num' , '=', 'vendor.vend_num')
        ->select(
            'po.po_num as po_number',
            'po.id as ID',
            'po.vend_num as kode_vendor',
            'po_line.item as name_item',
            'po_line.po_line as po_line',
            'vendor.vend_name as name_vendor',
            'vendor.vend_email as emails',
            'vendor.buyer_email as buyers',
            DB::raw('datediff(current_date(), po_line.created_at) as selisih'
        ))
        // ->whereRaw('datediff(current_date(), po_line.created_at) <= ?', [$reminderDay->first()['value']])
        ->where('po_line.accept_flag', 0)
        ->where('po_line.status', 'O')
        ->get();
        $poNumberGrouped = collect($dataPo);
        $groupByPoLine = $poNumberGrouped->unique(function ($item) {
            return $item['po_number'].$item['ID'];
        })->toArray(); // data ini nanti dipakai buat menemukan ID untuk dikirim ke email

        if(count($groupByPoLine) > 0){
            # jika ada datanya
            foreach($groupByPoLine as $poLine){
                $id = $poLine['ID'];
                $poNumber = $poLine['po_number'];

                $URL = env('APP_URL_PRODUCTION') . "/purchase-order/{$id}/show";

                $titleEmail = 'Auto Accept PO';
                $messageEmail = 'PO '. $poNumber.'-'.$poLine['po_line'].' anda telah diaccept oleh sistem, anda dapat mengklik tombol dibawah ini untuk melihatnya.';
                if ($poLine['selisih'] <= $reminderDay->first()['value']) {
                    $titleEmail = 'Reminder Accept PO';
                    $messageEmail = 'Anda memiliki PO '. $poNumber.'-'.$poLine['po_line'].'. Silahkan accept PO tersebut melalui link yang kami sediakan : ';
                }

                if($poLine['emails'] != null && $poLine['selisih'] >= 0){
                    $pecahEmailVendor = (new Constant())->emailHandler($poLine['emails'], 'array');
                    $pecahEmailBuyer = (new Constant())->emailHandler($poLine['buyers'], 'array');
                    
                    $details = [
                        'buyer_email' => $pecahEmailBuyer,
                        'po_num' => $poNumber,
                        'type' => 'reminder_po',
                        'title' =>  $titleEmail,
                        'message' => $messageEmail,
                        'url_button' => $URL.'?prev_session=true' //url("admin/purchase-order/{$po->ID}/show")
                    ];
                    try{
                        Mail::to($pecahEmailVendor)
                        ->cc($pecahEmailBuyer)
                        ->send(new ReminderAcceptPo($details));
                    }
                    catch(Exception $e){
                        $subject =  $titleEmail;
                        $pecahEmailVendor = implode(", ", explode(';', $poLine['emails']));
                        $pecahEmailBuyer = ($poLine['buyers'] != null) ?  implode(", ", explode(';', $poLine['buyers'])) : '';
                            
                        (new EmailLogWriter())->create($subject, $pecahEmailVendor, $e->getMessage(), $pecahEmailBuyer);
                        DB::commit();
                            
                        return Command::FAILURE;
                    }
                }

                \App\Models\PurchaseOrderLine::where('po_num', $poNumber)
                ->whereRaw('datediff(current_date(), po_line.created_at) > ?', [$reminderDay->first()['value']])
                ->where('po_line', $poLine['po_line'])
                ->where('accept_flag', 0)
                ->where('status', 'O')
                ->update([
                    'accept_flag' => 1,
                    'read_at' => now()
                ]);
                $this->info("Success PO ". $poNumber."-".$poLine['po_line']."::".$poLine['selisih']); 
            }
        }
        return Command::SUCCESS;
    }
}
