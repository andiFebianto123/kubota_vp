<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class ChangeTempUploadDeliveries1Table extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('temp_upload_deliveries', function (Blueprint $table) {
            $table->renameColumn('order_qty', 'shipped_qty');
            $table->text('serial_number')->change();
            $table->renameColumn('serial_number', 'data_attr');
        });
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
