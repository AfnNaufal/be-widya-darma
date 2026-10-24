<?php

namespace Database\Seeders;

use App\Models\Admin;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Admin::updateOrCreate(
            ['email' => 'admin@widyadarma.sch.id'],
            [
                'name' => 'Administrator SMA Widya Darma',
                'password' => Hash::make('password123'),
            ]
        );
    }
}
