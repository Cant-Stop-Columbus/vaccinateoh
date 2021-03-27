<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

use App\Models\LocationType;

class LocationTypeSeeder extends Seeder
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
                'name' => 'Healthcare',
                'short' => 'h',
                'slug' => 'healthcare',
            ],
            [
                'name' => 'Pharmacy',
                'short' => 'p',
                'slug' => 'pharmacy',
            ],
            [
                'name' => 'Local Health Department',
                'short' => 'd',
                'slug' => 'dept',
            ],
        ];
        foreach($data as $method) {
            LocationType::firstOrCreate([
                'name' => $method['name'],
            ],[
                'short' => $method['short'],
                'slug' => $method['slug'],
            ]);
        }
    }
}
