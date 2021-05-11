<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use App\Models\User;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('users')->insert([
            'email' => 'admin@larablog.dev',
            'username' => 'admin',
            'firstname' => 'Jean',
            'lastname' => 'Dupont',
            'password' => bcrypt('admin'),
            'created_at' => now()
        ]);
        
        User::factory()->count(100)->create();
    }
}
