<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

/**
 * TROV runs on real accounts created through the sign-up page, so there is no
 * demo seed data. This seeder is intentionally empty; `php artisan db:seed`
 * does nothing and will not create any users.
 */
class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // No seed data. Owners and video editors register themselves at /register.
    }
}
