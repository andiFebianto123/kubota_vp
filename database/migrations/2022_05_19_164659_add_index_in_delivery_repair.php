<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddIndexInDeliveryRepair extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('delivery_repair', function (Blueprint $table) {            
            $table->index('ds_num_reject');
            $table->index('ds_line_reject');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('delivery_repair', function (Blueprint $table) {
            // $table->dropIndex(['po_num', 'po_line', 'executed_flag', 'validate_by_fa_flag', 'payment_plan_date']); // Drops index 'geo_state_index'
            $table->dropIndex(['ds_num_reject']); // Drops index
            $table->dropIndex(['ds_line_reject']);
        });
    }
}
