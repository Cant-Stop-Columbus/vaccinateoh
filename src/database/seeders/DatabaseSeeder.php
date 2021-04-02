<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        //$this->call(LocationSeeder::class);
        $this->call(LocationTypeSeeder::class);
        $this->call(DataUpdateMethodSeeder::class);
        $this->call(AppointmentTypeSeeder::class);
        $this->call(LocationSourceSeeder::class);
    }
}
