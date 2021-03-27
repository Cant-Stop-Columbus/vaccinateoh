<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

use App\Models\LocationSource;

class LocationSourceSeeder extends Seeder
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
                'name' => 'ODH',
                'short' => 'o',
            ],
            [
                'name' => 'ArmorVax',
                'short' => 'av',
            ],
        ];

        foreach($data as $item) {
            LocationSource::firstOrCreate(['name' => $item['name']],['short' => $item['short']]);
        }
    }
}
