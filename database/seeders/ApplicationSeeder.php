<?php

namespace Database\Seeders;

use App\Models\Application;
use App\Models\Job;
use App\Services\ScoringService;
use Illuminate\Database\Seeder;

/**
 * ============================================================================
 * A SEEDER FOR **RELATED** DATA - the harder, more interesting case.
 * ============================================================================
 *
 * JobPortalSeeder creates rows that depend on nothing. This one creates rows
 * that need a Job to already exist. Three things follow from that:
 *
 * [LEARN] 1. ORDER MATTERS. In DatabaseSeeder this must be called AFTER
 *            JobPortalSeeder, or every lookup below returns null.
 *
 * [LEARN] 2. LOOK THE PARENT UP, DO NOT HARD-CODE ITS ID. Writing
 *            'job_id' => 1 breaks the moment anyone reorders the jobs. Find it
 *            by something stable - here, the title.
 *
 * [LEARN] 3. REUSE YOUR REAL LOGIC. We call the same ScoringService the
 *            controller calls. If we copy-pasted the +3/+2/+1 rules in here,
 *            the seeded scores could silently drift away from the live ones,
 *            and your demo would be lying.
 */
class ApplicationSeeder extends Seeder
{
    public function run(): void
    {
        /*
        | [LEARN] app() asks Laravel's container to build the class for you.
        |         It is the seeder equivalent of constructor injection - a
        |         seeder's run() cannot type-hint dependencies, so you resolve
        |         them by hand.
        */
        $scoring = app(ScoringService::class);

        /*
        | Chosen to cover EVERY combination of the two rules, so the Review
        | table proves the whole scoring system at a glance:
        |
        |   job location  +3 Remote / +2 Dar / +1 elsewhere
        |   disability    +3 or 0
        */
        $candidates = [
            //  name,              email,                    job title,           disability
            ['Ally Mdoka',       'amdoka@example.com',     'Software Engineer', false],
            ['Neema Kileo',      'nkileo@example.com',     'Software Engineer', true],
            ['Juma Hassan',      'jhassan@example.com',    'HR Officer',        false],
            ['Peter Msigwa',     'pmsigwa@example.com',    'HR Officer',        true],
            ['Grace Mwakalinga', 'gmwakalinga@example.com','Field Officer',     true],
            ['Sara Ndulu',       'sndulu@example.com',     'Field Officer',     false],
        ];

        foreach ($candidates as [$name, $email, $jobTitle, $hasDisability]) {
            $job = Job::where('title', $jobTitle)->first();

            // [LEARN] Defensive: if someone edits JobPortalSeeder and renames a
            //         job, skip rather than crash the whole seed run.
            if (! $job) {
                $this->command->warn("Skipped {$name}: no job titled \"{$jobTitle}\".");
                continue;
            }

            $resumeText = "{$name} has practical experience relevant to the {$jobTitle} role, "
                . 'including PHP, Laravel, React and relational database design.';

            /*
            | [LEARN] firstOrCreate, NOT create.
            |         The applications table has a unique index on
            |         (job_id, candidate_email). Running this seeder twice with
            |         create() would throw a UNIQUE constraint violation.
            |         firstOrCreate makes the seeder IDEMPOTENT - safe to re-run.
            |
            |         First argument  = how to find an existing row
            |         Second argument = what to write if it is not there
            */
            Application::firstOrCreate(
                [
                    'job_id' => $job->id,
                    'candidate_email' => $email,
                ],
                [
                    'candidate_name' => $name,
                    'resume_text' => $resumeText,
                    'has_disability' => $hasDisability,

                    // The spread merges the four score columns straight in,
                    // because evaluate() returns keys named after the columns.
                    ...$scoring->evaluate($job, $resumeText, $hasDisability),
                ],
            );
        }

        $this->command->info('Seeded '.count($candidates).' applications.');
    }
}
