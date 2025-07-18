<?php

namespace Database\Seeders;

use App\Models\City;
use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // User::factory(10)->create();


        // Crear roles si no existen
        \Spatie\Permission\Models\Role::firstOrCreate(['name' => 'Administrador']);
        \Spatie\Permission\Models\Role::firstOrCreate(['name' => 'Moderador']);
        \Spatie\Permission\Models\Role::firstOrCreate(['name' => 'Tienda']);

        $user = User::factory()->create([
            'name' => 'Timoteo',
            'email' => 'edgartimoteo@gmail.com',
            'password' => bcrypt('amorjanet123'),
        ]);

        // Asignar rol Administrador al usuario creado
        $user->assignRole('Administrador');

        // Crear ciudades
        $ciudades = ['Cuenca', 'Ambato', 'Quito'];
        foreach ($ciudades as $ciudad) {
            City::create(['user_id' => $user->id, 'name' => $ciudad]);
        }

        // Crear versiones de app
        \App\Models\AppVersion::create([
            'platform' => 'android',
            'min_version' => '1.0.0',
            'latest_version' => '1.0.1',
            'update_url' => 'https://ninari.org/'
        ]);
        \App\Models\AppVersion::create([
            'platform' => 'ios',
            'min_version' => '1.0.0',
            'latest_version' => '1.0.1',
            'update_url' => 'https://ninari.org/'
        ]);
    }
}
