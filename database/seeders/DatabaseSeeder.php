<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     *
     * [LEARN] This is the ONLY seeder `--seed` runs. Every other seeder has to
     *         be invoked from here with $this->call(). A seeder you wrote but
     *         never called is the classic "why is my database empty" bug -
     *         your VacancySeeder is still empty and uncalled, for example.
     */
    public function run(): void
    {
        User::factory()->create([
            'name' => 'Test User',
            'email' => 'test@example.com',
            'role' => 'admin',
        ]);

        /*
        | [LEARN] ***ORDER MATTERS.*** These run top to bottom. ApplicationSeeder
        |         looks up Jobs by title, so JobPortalSeeder must create them
        |         first. Swap these two lines and every application is skipped.
        */
        $this->call([
            JobPortalSeeder::class,
            ApplicationSeeder::class,
        ]);
    }
}
