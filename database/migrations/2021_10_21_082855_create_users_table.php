<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateUsersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('username');
            $table->string('email')->unique();
            $table->unsignedBigInteger('vendor_id')->nullable();
            $table->unsignedBigInteger('role_id')->nullable();
            $table->string('password');
            $table->integer('is_active')->default(1);
            $table->dateTime('last_login')->nullable();
            $table->string('user_agent')->nullable();
            $table->string('ip')->nullable();
            $table->dateTime('last_update_password')->nullable();
            $table->string('two_factor_code')->nullable();
            $table->string('two_factor_url')->nullable();
            $table->dateTime('two_factor_expires_at')->nullable();
            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->timestamps();
            $table->softDeletes();


            $table->foreign('role_id')
            ->references('id')
            ->on('roles')
            ->onUpdate('cascade');

            $table->foreign('vendor_id')
            ->references('id')
            ->on('vendors')
            ->onUpdate('cascade');
        });


    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('users');
    }
}
