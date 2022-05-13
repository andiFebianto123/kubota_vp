<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateStatusTempUploadDeliveriesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('status_temp_upload_deliveries', function (Blueprint $table) {
            $table->increments('id');
            $table->string('po_num');
            $table->integer('po_line');
            $table->integer('user_id');
            $table->double('shipped_qty')->nullable();
            $table->text('data_attr')->nullable();
            $table->dateTime('delivery_date')->nullable();
            $table->string('petugas_vendor')->nullable();
            $table->string('no_surat_jalan_vendor')->nullable();
            $table->text('message')->nullable();
            $table->integer('created_by')->nullable();
            $table->integer('updated_by')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('status_temp_upload_deliveries');
    }
}
