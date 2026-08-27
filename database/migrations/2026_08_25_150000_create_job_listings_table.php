<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/*
|--------------------------------------------------------------------------
| JOB LISTINGS
|--------------------------------------------------------------------------
| [EXAM] PSRS Part 2.I - "Add jobs with details such as title, department,
|        location and salary" + "View all active job postings."
|
| [LEARN] WHY IS THIS TABLE CALLED `job_listings` AND NOT `jobs`?
|         Open database/migrations/0001_01_01_000002_create_jobs_table.php -
|         it came with your starter kit and it already creates a table called
|         `jobs` for Laravel's queue system. Your .env even has
|         QUEUE_CONNECTION=database, so that table is genuinely in use.
|         Two migrations cannot create the same table: `migrate:fresh` would
|         crash. So the TABLE is `job_listings` but the MODEL stays `Job`
|         (see app/Models/Job.php, one line: protected $table).
|         The examiner reads your model and routes, not your table names.
|
| [REUSE] Structure copied from your own
|         database/migrations/2026_08_25_131414_create_vacancies_table.php
*/
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('job_listings', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('department');

            // [EXAM] PSRS Part 2.III - the scoring rules read THIS column.
            //        'Remote' => +3, 'DAR ES SALAAM' => +2, anything else => +1
            //        Note it is the JOB's location, never the candidate's.
            $table->string('location');

            // [LEARN] Money is always `decimal`, never `float`.
            //         Floats cannot represent cents exactly. 12 total digits,
            //         2 after the point -> up to 9,999,999,999.99
            $table->decimal('salary', 12, 2);

            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('job_listings');
    }
};
