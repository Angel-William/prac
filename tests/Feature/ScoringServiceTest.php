<?php

use App\Models\Job;
use App\Services\ScoringService;

/*
|--------------------------------------------------------------------------
| [LEARN] WHY WRITE ONE TEST IN A TIMED EXAM?
|--------------------------------------------------------------------------
| Because a passing test is the only way to PROVE your scoring rules are right
| without clicking through the UI. It takes four minutes and it is
| disproportionately persuasive in a code review.
|
| [REUSE] Your starter kit ships with Pest (see tests/Pest.php). `it(...)` and
|         `expect(...)` are Pest, not PHPUnit. Run everything with:
|             php artisan test
|         or just this file:
|             php artisan test --filter=ScoringService
*/

it('gives Remote jobs 3 priority points', function () {
    $job = new Job(['location' => 'Remote']);

    expect(app(ScoringService::class)->locationPriority($job))->toBe(3);
});

it('gives Dar es Salaam jobs 2 priority points, case-insensitively', function () {
    $job = new Job(['location' => 'dar es salaam']);

    expect(app(ScoringService::class)->locationPriority($job))->toBe(2);
});

it('gives every other location 1 priority point', function () {
    $job = new Job(['location' => 'Mwanza']);

    expect(app(ScoringService::class)->locationPriority($job))->toBe(1);
});

it('adds 3 points for a candidate with a disability', function () {
    $scoring = app(ScoringService::class);

    expect($scoring->disabilityPriority(true))->toBe(3)
        ->and($scoring->disabilityPriority(false))->toBe(0);
});

it('computes final score as resume + location + disability', function () {
    // [EXAM] PSRS 2.IV.c - the formula, asserted.
    expect(app(ScoringService::class)->finalScore(7, 3, 3))->toBe(13);
});

it('evaluates a whole application into the exact database columns', function () {
    $job = new Job(['location' => 'Remote']);

    $scores = app(ScoringService::class)->evaluate($job, 'Ten years of PHP and Java.', true);

    expect($scores)->toHaveKeys([
        'resume_score', 'location_priority', 'disability_priority', 'final_score',
    ])
        ->and($scores['location_priority'])->toBe(3)
        ->and($scores['disability_priority'])->toBe(3)
        // resume_score is mocked 1-10, so assert the RELATIONSHIP, not a value.
        ->and($scores['final_score'])->toBe($scores['resume_score'] + 6);
});
