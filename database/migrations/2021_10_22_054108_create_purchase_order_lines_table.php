<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePurchaseOrderLinesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('po_line', function (Blueprint $table) {
            $table->engine = 'MyISAM';
            $table->integer('id');
            $table->string('po_num')->length(10);
            $table->integer('po_line');
            $table->integer('po_release');
            $table->integer('po_change');
            $table->dateTime('po_change_date')->nullable();
            $table->string('item')->nullable();
            $table->string('item_ptki')->nullable();
            $table->string('w_serial')->nullable();
            $table->string('description')->nullable();
            $table->double('order_qty')->nullable();
            $table->integer('inspection_flag')->nullable();
            $table->string('u_m')->nullable();
            $table->dateTime('due_date')->nullable();
            $table->double('unit_price')->nullable();
            $table->string('wh')->nullable();
            $table->string('location')->nullable();
            $table->string('tax_status')->nullable();
            $table->string('currency')->nullable();
            $table->string('item_alias')->nullable();
            $table->string('status')->nullable();
            $table->dateTime('production_date')->nullable();
            $table->integer('accept_flag')->default(0);
            $table->integer('outhouse_flag')->default(0);
            $table->string('reason')->nullable();
            $table->string('read_by')->nullable();
            $table->dateTime('read_at')->nullable();
            $table->integer('created_by')->nullable();
            $table->integer('updated_by')->nullable();
            $table->dateTime('created_at')->nullable();
            $table->dateTime('updated_at')->nullable();

            $table->primary(['id','po_num', 'po_line', 'po_release', 'po_change']);
        });
        Schema::table('po_line', function (Blueprint $table) {
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
        Schema::dropIfExists('po_line');
    }
}
