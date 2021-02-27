<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

use App\Models\Location;

class GeocodeCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'geocode {--county=} {--name=} {--force}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Geocode locations missing lat/lng';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $county = $this->option('county');
        $force = $this->option('force') ? true : false;
        $name = $this->option('name');

        $locations = Location::whereRaw('1=1');

        if(!empty($county)) {
            $locations = $locations->where('county','like','%' . $county . '%');
        }

        if(!empty($name)) {
            $locations = $locations->where('name','like','%' . $name . '%');
        }

        $this->withProgressBar($locations->get(), function($loc) use($force) {
            $loc->geocode($force);
        });
    }
}
