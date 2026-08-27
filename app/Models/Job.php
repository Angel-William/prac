<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * [EXAM] PSRS Part 2.I - a job posting.
 *
 * [REUSE] Same shape as your app/Models/Vacancy.php, just filled in properly.
 */
class Job extends Model
{
    /**
     * [LEARN] THE ONE LINE THAT MAKES THE NAME `Job` POSSIBLE.
     *
     * By convention Eloquent turns the model name `Job` into the table `jobs`.
     * But `jobs` is taken by Laravel's queue. This one property redirects the
     * model at our own table, so every other file - controllers, routes, React
     * pages, the examiner's eyes - just sees "Job".
     */
    protected $table = 'job_listings';

    /**
     * [LEARN] MASS ASSIGNMENT PROTECTION.
     *
     * Job::create($request->all()) will only ever write these columns. If an
     * attacker POSTs `id` or `created_at`, Laravel silently drops them.
     * Your Vacancy model has this too - it is not optional boilerplate.
     */
    protected $fillable = [
        'title',
        'department',
        'location',
        'salary',
        'is_active',
    ];

    /**
     * [LEARN] CASTS turn raw database values into real PHP types.
     * Without this, `is_active` comes back from SQLite as the integer 1,
     * and `$job->is_active === true` would be false. Casts fix that.
     */
    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'salary' => 'decimal:2',
        ];
    }

    /**
     * [EXAM] "A Job has many Applications" - the relationship the paper implies.
     *
     * [LEARN] Because our table is `job_listings` but the foreign key on
     *         `applications` is `job_id`, Eloquent's guess (`job_id`) happens to
     *         be right. If you ever rename the model, name the key explicitly:
     *         hasMany(Application::class, 'job_id')
     */
    public function applications(): HasMany
    {
        return $this->hasMany(Application::class);
    }

    /**
     * [LEARN] A QUERY SCOPE. Anywhere you write Job::active()->get(), Laravel
     *         calls this and appends the where clause. It keeps
     *         "what does active mean" in ONE place instead of scattered
     *         ->where('is_active', true) all over your controllers.
     *
     * [EXAM] PSRS Part 2.I - "View all ACTIVE job postings."
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}
