<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddFileSuratJalanToDeliveryStatus extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('delivery_status', function (Blueprint $table) {
            //
           $table->string('invoice')->nullable()->after('file_faktur_pajak');
            $table->string('file_surat_jalan')->nullable()->after('invoice');
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
            //
           $table->dropColumn('invoice');
            $table->dropColumn('file_surat_jalan');
        });
    }
}
