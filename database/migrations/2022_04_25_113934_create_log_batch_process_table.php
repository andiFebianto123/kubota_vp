<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateLogBatchProcessTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('log_batch_process', function (Blueprint $table) {
            $table->id();
            $table->text('mail_to')->nullable();
            $table->text('mail_cc')->nullable();
            $table->text('mail_reply_to')->nullable();
            $table->string('po_num')->nullable();
            $table->text('error_message')->nullable();
            $table->string('type')->nullable();

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
        Schema::dropIfExists('log_batch_process');
    }
}
