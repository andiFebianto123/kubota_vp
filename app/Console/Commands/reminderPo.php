<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use App\Mail\vendorNewPo;
use Log;

class reminderPo extends Command
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
        ->join('users', 'vendor.id', '=', 'users.vendor_id')
        ->select(
            'po.po_num as po_number',
            'po.id as ID',
            'po.vend_num as kode_vendor',
            'po_line.item as name_item',
            'vendor.vend_name as name_vendor',
            'users.email as email',
            DB::raw('datediff(current_date(), po_line.created_at) as selisih'
        ))
        ->whereRaw('datediff(current_date(), po_line.created_at) >= ?', [$reminderDay->first()['value']])
        ->where('po_line.accept_flag', 0)
        ->get();
        $po_number_grouped = collect($dataPo);
        $groupByPoLine = $po_number_grouped->unique(function ($item) {
            return $item['po_number'].$item['ID'];
        })->toArray(); // data ini nanti dipakai buat menemukan ID untuk dikirim ke email

        if(count($groupByPoLine) > 0){
            # jika ada datanya
            foreach($groupByPoLine as $po_line){
                $id = $po_line['ID'];
                $poNumber = $po_line['po_number'];
                $emailVendor = $po_line['email'];

                $updateDataPoLine = \App\Models\PurchaseOrderLine::where('po_num', $poNumber)
                ->whereRaw('datediff(current_date(), po_line.created_at) >= ?', [$reminderDay->first()['value']])
                ->where('accept_flag', 0)
                ->update([
                    'accept_flag' => 1,
                    'read_at' => now()
                ]);

                $URL = url("/kubota_vp/kubota-vendor-portal/public/admin/purchase-order/{$id}/show");
                $details = [
                    'type' => 'reminder_po',
                    'title' => 'Reminder accept PO',
                    'message' => 'Semua data PO Line anda telah di accept, Anda dapat mengklik tombol dibawah ini.',
                    'url_button' => $URL //url("admin/purchase-order/{$po->ID}/show")
                ];
                Mail::to($emailVendor)->send(new vendorNewPo($details));
                Log::info("Sukses kirim ke email {$emailVendor}");
            }
        }
        return Command::SUCCESS;
    }
}
