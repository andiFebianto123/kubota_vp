<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateLockedAccountsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('locked_accounts', function (Blueprint $table) {
            $table->id();
            $table->string('account');
            $table->string('type');
            $table->string('ip')->nullable();
            $table->string('ua')->nullable();
            $table->string('detail')->nullable();
            $table->string('lock_start')->nullable();
            $table->string('lock_end')->nullable();
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
        Schema::dropIfExists('locked_accounts');
    }
}
