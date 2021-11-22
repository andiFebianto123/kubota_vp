<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateDeliveryRepairsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('delivery_repair', function (Blueprint $table) {
            $table->integer('id');
            $table->string('ds_num')->length(15);
            $table->integer('ds_line');
            $table->string('reason_num')->length(5);
            $table->string('repair_num')->length(15);
            $table->string('repair_type');
            $table->double('repair_qty');
            $table->dateTime('repair_date');
            $table->dateTime('inspection_date');
            $table->string('ref_num')->nullable();
            $table->integer('ref_line')->nullable();
            $table->integer('created_by')->nullable();
            $table->integer('updated_by')->nullable();
            $table->dateTime('created_at')->nullable();
            $table->dateTime('updated_at')->nullable();

            $table->primary(['id', 'ds_num', 'ds_line', 'reason_num', 'repair_num']);
        });
        Schema::table('delivery_repair', function (Blueprint $table) {
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
        Schema::dropIfExists('delivery_repair');
    }
}
