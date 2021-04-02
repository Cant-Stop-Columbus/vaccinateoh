<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateDataUpdateMethods extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('data_update_methods', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('short', 10)->nullable();
            $table->timestamps();
        });
        Schema::table('locations', function(Blueprint $table) {
            $table->foreignId('data_update_method_id')->nullable();
            $table->foreign('data_update_method_id')->references('id')->on('data_update_methods');
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
            $table->dropForeign(['data_update_method_id']);
            $table->dropColumn('data_update_method_id');
        });
        Schema::dropIfExists('data_update_methods');
    }
}
