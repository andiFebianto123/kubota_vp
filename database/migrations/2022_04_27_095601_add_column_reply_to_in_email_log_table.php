<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddColumnReplyToInEmailLogTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('email_log', function (Blueprint $table) {
            $table->text('from')->nullable()->change();
            $table->text('to')->nullable()->change();
            $table->text('cc')->nullable()->change();
            $table->text('bcc')->nullable()->change();
            if(!Schema::hasColumn('email_log', 'reply_to')){
                $table->text('reply_to')->nullable();
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
        Schema::table('in_email_log', function (Blueprint $table) {
            //
        });
    }
}
