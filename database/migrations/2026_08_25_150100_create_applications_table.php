<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/*
|--------------------------------------------------------------------------
| APPLICATIONS
|--------------------------------------------------------------------------
| [EXAM] PSRS Part 2.II  - candidate submits name, email, position, resume text
| [EXAM] PSRS Part 2.IV  - resume score, priority points, final score
| [EXAM] PSRS constraint - "Ensure each candidate can apply only once per job"
| [EXAM] UTUMISHI        - priority by disability
*/
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('applications', function (Blueprint $table) {
            $table->id();

            // [LEARN] foreignId()->constrained() normally GUESSES the table from
            //         the column name: `job_id` -> `jobs`. Our table is
            //         `job_listings`, so we MUST name it explicitly. Forgetting
            //         this is the single most common error with this setup:
            //         "SQLSTATE... no such table: jobs".
            $table->foreignId('job_id')
                ->constrained('job_listings')
                ->cascadeOnDelete();

            // [LEARN] Nullable link to the logged-in user. The exam only asks for
            //         name + email, but recording WHO applied costs one column
            //         and makes the app feel real.
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();

            $table->string('candidate_name');
            $table->string('candidate_email');
            $table->text('resume_text');

            // [EXAM] UTUMISHI - the affirmative-action flag.
            $table->boolean('has_disability')->default(false);

            // --- Scores. Written by App\Services\ScoringService, never by the user.
            $table->unsignedTinyInteger('resume_score')->nullable();        // 1-10
            $table->unsignedTinyInteger('location_priority')->nullable();   // 1-3
            $table->unsignedTinyInteger('disability_priority')->default(0); // 0 or 3
            $table->unsignedTinyInteger('final_score')->nullable();         // the sum

            $table->timestamps();

            // [EXAM] "Each candidate can apply only once per job."
            // [LEARN] Enforce rules in the DATABASE, not only in PHP. Validation
            //         can be bypassed (the API, a seeder, a race condition);
            //         a unique index cannot. We ALSO validate in
            //         StoreApplicationRequest so the user gets a friendly
            //         message instead of a 500 error page. Belt and braces.
            $table->unique(['job_id', 'candidate_email']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('applications');
    }
};
