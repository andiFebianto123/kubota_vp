<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddIndexInCreatedAtDeliveryStatus extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('delivery_status', function (Blueprint $table) {            
            $table->index('created_at');
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
            $table->dropIndex(['created_at']);
        });
    }
}
