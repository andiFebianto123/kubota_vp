<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddUrgentColumnToPoLineTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('po_line', function (Blueprint $table) {
            $table->integer('urgent_flag')->after('read_by')->default(0);
            $table->string('urgent_reason')->after('urgent_flag')->nullable();
            $table->dateTime('urgent_date')->after('urgent_reason')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
    }
}
