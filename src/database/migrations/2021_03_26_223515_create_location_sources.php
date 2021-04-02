<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateLocationSources extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('location_sources', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('short', 10)->nullable();
            $table->timestamps();
        });
        Schema::table('locations', function(Blueprint $table) {
            $table->foreignId('location_source_id')->nullable();
            $table->foreign('location_source_id')->references('id')->on('location_sources');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('locations', function(Blueprint $table) {
            $table->dropForeign(['location_source_id']);
            $table->dropColumn('location_source_id');
        });
        Schema::dropIfExists('location_sources');
    }
}
