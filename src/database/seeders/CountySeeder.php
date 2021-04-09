<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

use App\Models\County;

class CountySeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $counties = [
            ['Adams',6],
            ['Allen',1],
            ['Ashland',5],
            ['Ashtabula',2],
            ['Athens',7],
            ['Auglaize',1],
            ['Belmont',8],
            ['Brown',6],
            ['Butler',6],
            ['Carroll',5],
            ['Champaign',3],
            ['Clark',3],
            ['Clermont',6],
            ['Clinton',6],
            ['Columbiana',5],
            ['Coshocton',8],
            ['Crawford',4],
            ['Cuyahoga',2],
            ['Darke',3],
            ['Defiance',1],
            ['Delaware',4],
            ['Erie',1],
            ['Fairfield',4],
            ['Fayette',4],
            ['Franklin',4],
            ['Fulton',1],
            ['Gallia',7],
            ['Geauga',2],
            ['Greene',3],
            ['Guernsey',8],
            ['Hamilton',6],
            ['Hancock',1],
            ['Hardin',4],
            ['Harrison',8],
            ['Henry',1],
            ['Highland',6],
            ['Hocking',7],
            ['Holmes',5],
            ['Huron',1],
            ['Jackson',7],
            ['Jefferson',8],
            ['Knox',4],
            ['Lake',2],
            ['Lawrence',7],
            ['Licking',4],
            ['Logan',4],
            ['Lorain',2],
            ['Lucas',1],
            ['Madison',4],
            ['Mahoning',5],
            ['Marion',4],
            ['Medina',5],
            ['Meigs',7],
            ['Mercer',1],
            ['Miami',3],
            ['Monroe',8],
            ['Montgomery',3],
            ['Morgan',8],
            ['Morrow',4],
            ['Muskingum',8],
            ['Noble',8],
            ['Ottawa',1],
            ['Paulding',1],
            ['Perry',8],
            ['Pickaway',4],
            ['Pike',7],
            ['Portage',5],
            ['Preble',3],
            ['Putnam',1],
            ['Richland',5],
            ['Ross',7],
            ['Sandusky',1],
            ['Scioto',7],
            ['Seneca',1],
            ['Shelby',3],
            ['Stark',5],
            ['Summit',5],
            ['Trumbull',5],
            ['Tuscarawas',5],
            ['Union',4],
            ['Van Wert',1],
            ['Vinton',7],
            ['Warren',6],
            ['Washington',8],
            ['Wayne',5],
            ['Williams',1],
            ['Wood',1],
            ['Wyandot',4],
        ];

        County::truncate();
        foreach($counties as $county) {
            County::create(['name'=>$county[0],'region'=>$county[1]]);
        }

    }
}
