<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class DropBodyAndAttachmentInEmailLog extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if(Schema::hasColumn('email_log', 'body')){
            Schema::table('email_log', function (Blueprint $table) {
                $table->dropColumn('body');
            });
        }
        if(Schema::hasColumn('email_log', 'attachments')){
            Schema::table('email_log', function (Blueprint $table) {
                $table->dropColumn('attachments');
            });
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('email_log', function (Blueprint $table) {
            //
        });
    }
}
