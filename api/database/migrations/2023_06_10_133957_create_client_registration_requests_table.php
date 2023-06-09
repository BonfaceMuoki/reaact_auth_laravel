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
    public function up()
    {
        Schema::create('client_registration_requests', function (Blueprint $table) {
            $table->id();
            $table->string('invite_email');
            $table->string('contact_person_name');
            $table->string('contact_person_phone');
            $table->string('institution_name');
            $table->string('type')->default("Court"); 
            $table->enum('status',['Requested','Approved','Rejected','Registered']);  
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
        Schema::dropIfExists('client_registration_requests');
    }
};
