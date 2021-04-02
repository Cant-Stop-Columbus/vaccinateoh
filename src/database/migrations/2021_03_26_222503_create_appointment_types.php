<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAppointmentTypes extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('appointment_types', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('short', 10)->nullable();
            $table->timestamps();
        });
        Schema::create('locations_appointment_types', function (Blueprint $table) {
            $table->foreignId('location_id')->nullable();
            $table->foreignId('appointment_type_id')->nullable();
            $table->foreign('location_id')->references('id')->on('locations');
            $table->foreign('appointment_type_id')->references('id')->on('appointment_types');
            $table->primary(['location_id','appointment_type_id']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('locations_appointment_types');
        Schema::dropIfExists('appointment_types');
    }
}
