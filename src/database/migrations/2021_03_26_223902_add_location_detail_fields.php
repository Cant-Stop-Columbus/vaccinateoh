<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddLocationDetailFields extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('locations', function (Blueprint $table) {
            $table->string('provider_url')->nullable();
            $table->string('provider_phone')->nullable();
            $table->string('system_type')->nullable();
            $table->string('update_method')->nullable();
            $table->string('alternate_addresses', 2000)->nullable();
            $table->foreignId('collector_user_id')->nullable();
            $table->foreign('collector_user_id')->references('id')->on('users');
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
            $table->dropForeign(['collector_user_id']);
            $table->dropColumn('provider_url');
            $table->dropColumn('provider_phone');
            $table->dropColumn('system_type');
            $table->dropColumn('update_method');
            $table->dropColumn('alternate_addresses');
            $table->dropColumn('collector_user_id');
        });
    }
}
