<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class ChangeDoubleColumnRangeAllTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::statement('alter table delivery modify order_qty DOUBLE(23,8) DEFAULT 0');
        DB::statement('alter table delivery modify unit_price DOUBLE(22,8) DEFAULT 0');
        DB::statement('alter table delivery modify shipped_qty DOUBLE(23,8) DEFAULT 0');

        DB::statement('alter table delivery_status modify shipped_qty DOUBLE(23,8) DEFAULT 0');
        DB::statement('alter table delivery_status modify received_qty DOUBLE(23,8) DEFAULT 0');
        DB::statement('alter table delivery_status modify rejected_qty DOUBLE(23,8) DEFAULT 0');
        DB::statement('alter table delivery_status modify unit_price DOUBLE(22,8) DEFAULT 0');
        DB::statement('alter table delivery_status modify total DOUBLE(22,8) DEFAULT 0');
        DB::statement('alter table delivery_status modify harga_sebelum_pajak DOUBLE(22,8) DEFAULT 0');
        DB::statement('alter table delivery_status modify ppn DOUBLE(22,8) DEFAULT 0');
        DB::statement('alter table delivery_status modify pph DOUBLE(22,8) DEFAULT 0');

        DB::statement('alter table po_line modify order_qty DOUBLE(23,8) DEFAULT 0');
        DB::statement('alter table po_line modify unit_price DOUBLE(22,8) DEFAULT 0');
        
        DB::statement('alter table vendor_item modify qty_per_box DOUBLE(23,8) DEFAULT 0');
        DB::statement('alter table vendor_item modify qty_per_box DOUBLE(23,8) DEFAULT 0');

        DB::statement('alter table delivery_repair modify repair_qty DOUBLE(23,8) DEFAULT 0');

        DB::statement('alter table delivery_reject modify rejected_qty DOUBLE(23,8) DEFAULT 0');

        DB::statement('alter table confirm_payment modify received_qty DOUBLE(23,8) DEFAULT 0');
        DB::statement('alter table confirm_payment modify rejected_qty DOUBLE(23,8) DEFAULT 0');
        DB::statement('alter table confirm_payment modify unit_price DOUBLE(22,8) DEFAULT 0');
        DB::statement('alter table confirm_payment modify harga_sebelum_pajak DOUBLE(22,8) DEFAULT 0');
        DB::statement('alter table confirm_payment modify ppn DOUBLE(22,8) DEFAULT 0');
        DB::statement('alter table confirm_payment modify pph DOUBLE(22,8) DEFAULT 0');

        DB::statement('alter table forecasts modify qty DOUBLE(22,8) DEFAULT 0');
}

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
    }
}
