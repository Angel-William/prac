<?php

namespace App\Policies;

use App\Models\Job;
use App\Models\User;

/**
 * [REUSE] You generated app/Policies/VacancyPolicy.php and left every method
 *         returning false. This is the filled-in version. Compare them
 *         side by side - that is what those stubs are for.
 *
 * [LEARN] MIDDLEWARE vs POLICY - the difference examiners ask about:
 *
 *           Middleware guards a ROUTE.   "Can this role reach /jobs/create?"
 *           Policy     guards a RECORD.  "Can this user edit THIS job?"
 *
 *         We use both. Middleware is the cheap outer gate; the policy is the
 *         precise inner one. Having both is a strong RBAC answer.
 *
 * [LEARN] You do NOT have to register this anywhere. Laravel auto-discovers
 *         App\Policies\JobPolicy for App\Models\Job by naming convention.
 *         Name it wrong and it is silently ignored - a nasty bug to hunt.
 */
class JobPolicy
{
    /** Everyone signed in may browse the job board. */
    public function viewAny(User $user): bool
    {
        return true;
    }

    /** Everyone signed in may open a single job and apply. */
    public function view(User $user, Job $job): bool
    {
        return true;
    }

    /** [EXAM] PSRS 1.c - only admin and editor may post jobs. */
    public function create(User $user): bool
    {
        return $user->canManageJobs();
    }

    public function update(User $user, Job $job): bool
    {
        return $user->canManageJobs();
    }

    /**
     * [LEARN] A record-level rule that middleware could never express:
     *         editors may not delete a job that already has applicants,
     *         because that would cascade-delete real candidate data.
     *         Admins still can. THIS is why policies exist.
     */
    public function delete(User $user, Job $job): bool
    {
        if ($user->isAdmin()) {
            return true;
        }

        return $user->canManageJobs() && $job->applications()->doesntExist();
    }

    /** [EXAM] PSRS 2.IV.d - only admin and editor may read the review summary. */
    public function review(User $user): bool
    {
        return $user->canManageJobs();
    }
}
