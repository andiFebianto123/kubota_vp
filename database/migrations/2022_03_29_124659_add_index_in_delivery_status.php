<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddIndexInDeliveryStatus extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('delivery_status', function (Blueprint $table) {            
            $table->index('po_num');
            $table->index('po_line');
            $table->index('item');
            $table->index('executed_flag');
            $table->index('validate_by_fa_flag');
            $table->index('payment_plan_date');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('delivery_status', function (Blueprint $table) {
            // $table->dropIndex(['po_num', 'po_line', 'executed_flag', 'validate_by_fa_flag', 'payment_plan_date']); // Drops index 'geo_state_index'
        });
    }
}
