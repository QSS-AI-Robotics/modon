<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Drone;
use Illuminate\Support\Str;

class DroneSeeder extends Seeder
{
    public function run()
    {
        $models = ['DJI Mavic 3', 'DJI Mini 2 SE', 'DJI Phantom 4 Pro', 'DJI Air 2S', 'DJI Avata'];

        // Insert 3 random drones
        for ($i = 0; $i < 3; $i++) {
            Drone::create([
                'model' => $models[array_rand($models)],
                'sr_no' => 'SN-' . strtoupper(Str::random(8)),
                'user_id' => 4 // pilot user
            ]);
        }
    }
}
