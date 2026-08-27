<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * [EXAM] PSRS Part 2.II + 2.IV - a candidate's application and its scores.
 */
class Application extends Model
{
    protected $fillable = [
        'job_id',
        'user_id',
        'candidate_name',
        'candidate_email',
        'resume_text',
        'has_disability',
        'resume_score',
        'location_priority',
        'disability_priority',
        'final_score',
    ];

    protected function casts(): array
    {
        return [
            'has_disability' => 'boolean',
        ];
    }

    /**
     * [EXAM] "An Application belongs to one Job."
     *
     * [LEARN] This is what lets you write $application->job->title in the
     *         controller and ->with('job') to eager-load. Without the
     *         relationship you would be writing manual joins.
     */
    public function job(): BelongsTo
    {
        return $this->belongsTo(Job::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
