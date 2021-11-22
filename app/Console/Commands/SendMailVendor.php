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
        ->select('po.id as ID')
        ->whereNull('email_flag');
        if($pos->count() > 0){
            # alias terdapat data yang kosong
            $getPo = $pos->get();
            foreach($getPo as $po){
                $details = [
                    'type' => 'reminder_po',
                    'title' => 'Ada PO baru',
                    'message' => 'Anda memiliki PO baru. Untuk melihat PO baru, Anda dapat mengklik tombol dibawah ini.',
                    'url_button' => url("admin/purchase-order/{$po->ID}/show")
                ];
                Mail::to('admin@ptki.com')->send(new vendorNewPo($details));
                $updatePo = DB::table('po')::find($po->ID)->update([
                    'email_flag' => now()
                ]);
            }
        }
        return Command::SUCCESS;
    }
}
