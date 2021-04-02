<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

use App\Models\DataUpdateMethod;

class DataUpdateMethodSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $data = [
            [
                'name' => 'Scraper',
                'short' => 's',
            ],
            [
                'name' => 'Manual',
                'short' => 'm',
            ],
            [
                'name' => 'Manual - Phone',
                'short' => 'mp',
            ],
            [
                'name' => 'Manual - Registration Required',
                'short' => 'mr',
            ],
        ];

        foreach($data as $method) {
            DataUpdateMethod::firstOrCreate(['name' => $method['name']],['short' => $method['short']]);
        }
    }
}
