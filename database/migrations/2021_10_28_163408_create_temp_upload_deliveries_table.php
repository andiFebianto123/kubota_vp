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
            $table->id();
            $table->bigInteger('po_line_id');
            $table->bigInteger('user_id');
            $table->double('order_qty');
            $table->string('serial_number');
            $table->string('petugas_vendor')->nullable();
            $table->string('no_surat_jalan_vendor')->nullable();
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
        Schema::dropIfExists('temp_upload_deliveries');
    }
}
