<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddSessionBatchProcessRevisionToPo extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('po', function (Blueprint $table) {
            if (!Schema::hasColumn('po', 'session_batch_process_revision')) {
                $table->integer('session_batch_process_revision')->after('session_batch_process')->default(0);
            } 
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('po', function (Blueprint $table) {
            //
        });
    }
}
