<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateDeliveryRejectsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('delivery_reject', function (Blueprint $table) {
            $table->integer('id');
            $table->string('ds_num')->length(15);
            $table->integer('ds_line');
            $table->string('reason_num')->length(5);
            $table->string('reason');
            $table->double('rejected_qty');
            $table->dateTime('inspection_date');
            $table->integer('created_by')->nullable();
            $table->integer('updated_by')->nullable();
            $table->dateTime('created_at')->nullable();
            $table->dateTime('updated_at')->nullable();

            $table->primary(['id', 'ds_num', 'ds_line', 'reason_num']);
        });
        Schema::table('delivery_reject', function (Blueprint $table) {
            $table->integer('id', true, true)->change();
        });

    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('delivery_reject');
    }
}
