<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAvailabilitiesArchive extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('availabilities_archive', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('location_id');
            $table->integer('doses')->nullable();
            $table->dateTime('availability_time')->nullable();
            $table->string('brand', 3)->nullable();
            $table->foreignId('updated_by_user_id')->nullable();
            $table->boolean('is_provider_update')->default(false);
            $table->softDeletes();
            $table->timestamps();
            $table->foreign('location_id')->references('id')->on('locations');
            $table->foreign('updated_by_user_id')->references('id')->on('users');
            $table->index(['location_id','availability_time']);
            $table->index(['availability_time']);
            $table->index(['updated_at']);
        });
        Schema::table('availabilities', function (Blueprint $table) {
            $table->index(['availability_time']);
            $table->index(['updated_at']);
        });

        DB::statement("CREATE VIEW availabilities_all AS SELECT location_id,doses,availability_time,brand,updated_by_user_id,is_provider_update,created_at,updated_at FROM availabilities
        UNION ALL
        SELECT location_id,doses,availability_time,brand,updated_by_user_id,is_provider_update,created_At,updated_at FROM availabilities_archive");
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('availabilities', function (Blueprint $table) {
            $table->dropIndex(['updated_at']);
            $table->dropIndex(['id','location_id','availability_time']);
            $table->dropIndex(['availability_time']);
            $table->index(['location_id','availability_time']);
        });
        DB::statement("DROP VIEW availabilities_all");
        Schema::dropIfExists('availabilities_archive');
    }
}
