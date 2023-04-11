<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up($table)
    {
        Schema::create('users_roles', function (Blueprint $table)
        {
        //
        $table->unsignedBigInteger('user_id');
        $table->unsignedBigInteger('organization_id');
        //FOREIGN KEY
        $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
        $table->foreign('organization_id')->references('id')->on('organizations')->onDelete('cascade');
        //PRIMARY KEYS
        $table->primary(['user_id', 'organization_id']);
    });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        //
    }
};
