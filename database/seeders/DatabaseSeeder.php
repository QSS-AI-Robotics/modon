<?php

namespace Database\Seeders;


use App\Models\UserType;
use App\Models\NavigationLink;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Create Navigation Links
        $links = [
            ['name' => 'Overview', 'url' => '/dashboard', 'sort_order' => 1],
            ['name' => 'Missions', 'url' => '/missions', 'sort_order' => 2],
            ['name' => 'Locations', 'url' => '/locations', 'sort_order' => 3],
            ['name' => 'Pilot', 'url' => '/pilot', 'sort_order' => 4],
        ];
    
        foreach ($links as $link) {
            NavigationLink::updateOrCreate(['url' => $link['url']], $link);
        }
    
        // Attach Links to UserType
        $adminType = UserType::firstOrCreate(['name' => 'admin']);
        $adminType->navigationLinks()->sync(NavigationLink::pluck('id')->toArray());
    }
}
