<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddLocationType extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('location_types', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('short')->nullable();
            $table->string('slug')->nullable();
            $table->timestamps();
        });

        Schema::table('locations', function (Blueprint $table) {
            $table->unsignedBigInteger('location_type_id')->nullable();
            $table->foreign('location_type_id')->references('id')->on('location_types');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('locations', function (Blueprint $table) {
            $table->dropForeign(['location_type_id']);
            $table->dropColumn('location_type_id');
        });

        Schema::drop('location_types');
    }
}
