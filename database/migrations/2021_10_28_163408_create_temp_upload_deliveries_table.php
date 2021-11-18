<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTempUploadDeliveriesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('temp_upload_deliveries', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('po_line_id');
            $table->integer('user_id');
            $table->double('order_qty');
            $table->string('serial_number');
            $table->string('petugas_vendor')->nullable();
            $table->string('no_surat_jalan_vendor')->nullable();
            $table->integer('created_by')->nullable();
            $table->integer('updated_by')->nullable();
            $table->dateTime('created_at')->nullable();
            $table->dateTime('updated_at')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('temp_upload_deliveries');
    }
}
