<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class ChangeSessionBatchProcessRevisionNullableToPo extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if(Schema::hasColumn('po','session_batch_process_revision')){
            Schema::table('po', function (Blueprint $table) {
                $table->dropColumn('session_batch_process_revision');
            });
        }
        Schema::table('po', function (Blueprint $table) {
            $table->bigInteger('session_batch_process_revision')->after('session_batch_process')->nullable();
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
