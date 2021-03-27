<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

use App\Models\AppointmentType;

class AppointmentTypeSeeder extends Seeder
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
                'name' => 'Web',
                'short' => 'web',
            ],
            [
                'name' => 'Phone',
                'short' => 'phone',
            ],
            [
                'name' => 'Walk-in',
                'short' => 'none',
            ],
        ];

        foreach($data as $item) {
            AppointmentType::firstOrCreate(['name' => $item['name']],['short' => $item['short']]);
        }
    }
}
