<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddDataAndIpToRevisionableTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('revisions', function (Blueprint $table) {
            if (!Schema::hasColumn('revisions', 'data')) {
                $table->text('data')->after('new_value')->nullable();
            } 
            if (!Schema::hasColumn('revisions', 'ip')) {
                $table->string('ip')->after('revisionable_type')->nullable();
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
    }
}
