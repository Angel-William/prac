<?php

namespace Database\Seeders;

use App\Models\Job;
use App\Models\User;
use Illuminate\Database\Seeder;

/**
 * ============================================================================
 * THE HIGHEST-VALUE FIVE MINUTES OF THE WHOLE EXAM.
 * ============================================================================
 *
 * [LEARN] Examiners mark what they SEE. A demo with three roles ready to log
 *         in and three jobs covering every scoring branch tells your story for
 *         you. Typing test data by hand while someone watches does not.
 *
 * Run it with:  php artisan migrate:fresh --seed
 */
class JobPortalSeeder extends Seeder
{
    public function run(): void
    {
        /*
        | [EXAM] PSRS 1.c - one user per role, so RBAC can be demonstrated.
        |
        | [REUSE] User::factory() comes from the starter kit
        |         (database/factories/UserFactory.php). It fills in the password
        |         - which is literally "password" - and marks the email verified.
        |         We only override the fields we care about.
        |
        | LOGINS FOR YOUR DEMO (write these on paper before you start):
        |     admin@psrs.go.tz  / password   -> can do everything
        |     editor@psrs.go.tz / password   -> can post jobs, cannot delete
        |                                       a job that has applicants
        |     viewer@psrs.go.tz / password   -> can only browse and apply
        */
        User::factory()->create([
            'name' => 'Amina Admin',
            'email' => 'admin@psrs.go.tz',
            'role' => 'admin',
        ]);

        User::factory()->create([
            'name' => 'Edwin Editor',
            'email' => 'editor@psrs.go.tz',
            'role' => 'editor',
        ]);

        User::factory()->create([
            'name' => 'Victor Viewer',
            'email' => 'viewer@psrs.go.tz',
            'role' => 'viewer',
        ]);

        /*
        | [EXAM] These are the exact jobs from the paper's "Sample Input Flow".
        |
        | [LEARN] ***WHY THESE THREE AND NOT ANY THREE?***
        |         They cover all three branches of the location rule, so your
        |         demo proves the whole rule without you having to type a new
        |         job live:
        |             Remote        -> +3
        |             DAR ES SALAAM -> +2
        |             Mwanza        -> +1  (the "any other location" branch)
        |         Pick your test data to prove your logic, not to look busy.
        */
        Job::create([
            'title' => 'Software Engineer',
            'department' => 'ICT',
            'location' => 'Remote',
            'salary' => 1200000,
        ]);

        Job::create([
            'title' => 'HR Officer',
            'department' => 'Human Resources',
            'location' => 'DAR ES SALAAM',
            'salary' => 750000,
        ]);

        Job::create([
            'title' => 'Field Officer',
            'department' => 'Operations',
            'location' => 'Mwanza',
            'salary' => 600000,
        ]);

        // An inactive job, to prove Job::active() actually filters.
        Job::create([
            'title' => 'Driver (closed)',
            'department' => 'Operations',
            'location' => 'Dodoma',
            'salary' => 450000,
            'is_active' => false,
        ]);

        /*
        | [LEARN] ***SEED ENOUGH ROWS TO PROVE YOUR PAGINATION.***
        |         JobController@index paginates at 5 per page. With only four
        |         jobs there would be exactly one page, the Previous/Next
        |         controls would never render, and you could not demonstrate the
        |         feature you just built. These six take the total to ten, so
        |         page 2 exists. Test data exists to prove behaviour.
        */
        $more = [
            ['Systems Analyst', 'ICT', 'DAR ES SALAAM', 950000],
            ['Database Administrator', 'ICT', 'Remote', 1100000],
            ['Procurement Officer', 'Finance', 'Dodoma', 800000],
            ['Accountant', 'Finance', 'Arusha', 850000],
            ['Records Officer', 'Human Resources', 'Mwanza', 550000],
            ['Legal Officer', 'Legal', 'DAR ES SALAAM', 1000000],
        ];

        foreach ($more as [$title, $department, $location, $salary]) {
            Job::create([
                'title' => $title,
                'department' => $department,
                'location' => $location,
                'salary' => $salary,
            ]);
        }
    }
}
