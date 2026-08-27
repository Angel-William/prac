<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreApplicationRequest;
use App\Models\Application;
use App\Models\Job;
use App\Services\ScoringService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;

/**
 * [EXAM] PSRS Part 2.II - Candidate Application Handling
 * [EXAM] PSRS Part 2.IV - Application Review
 */
class ApplicationController extends Controller
{
    /**
     * [LEARN] CONSTRUCTOR INJECTION / DEPENDENCY INJECTION.
     *
     *         You never write `new ScoringService()`. You ask for one in the
     *         constructor and Laravel's service container builds and hands it
     *         over. That is what makes the class testable: a test can pass in
     *         a fake scorer with a fixed resume score instead of a random one.
     *
     *         `private ScoringService $scoring` in the signature is PHP 8
     *         "constructor property promotion" - it declares the property AND
     *         assigns it in one line.
     */
    public function __construct(private ScoringService $scoring) {}

    /**
     * [EXAM] PSRS 2.II - "Allow a candidate to apply for a specific job."
     *
     * Read this method top to bottom - it is the heart of the whole exam.
     */
    public function store(StoreApplicationRequest $request, Job $job)
    {
        // 1. Validated data. The "apply only once" rule and the checkbox rule
        //    already ran inside StoreApplicationRequest.
        $data = $request->validated();

        // 2. Ask the service for every score. One call, four numbers back,
        //    keyed to match the table columns exactly.
        //    [EXAM] PSRS 2.III + 2.IV.a/b/c, and the UTUMISHI disability rule.
        $scores = $this->scoring->evaluate(
            job: $job,
            resumeText: $data['resume_text'],
            hasDisability: $request->boolean('has_disability'),
        );

        // 3. Save. Creating THROUGH the relationship ($job->applications())
        //    sets job_id automatically - you cannot attach it to the wrong job.
        //
        // [LEARN] The spread operator merges arrays. Later keys win, which is
        //         why has_disability below overrides whatever was in $data:
        //         $request->boolean() turns "1"/"on"/null into a real bool.
        $job->applications()->create([
            ...$data,
            'user_id' => $request->user()->id,
            'has_disability' => $request->boolean('has_disability'),
            ...$scores,
        ]);

        // [LEARN] back() returns to the page the form was posted from, so the
        //         candidate stays on the job they just applied for.
        return back()->with('success', 'Application processed successfully.');
    }

    /**
     * An applicant's own applications.
     *
     * [LEARN] THE SECURITY OF THIS PAGE IS THE where() CLAUSE, nothing else.
     *         There is no role middleware on the route because every applicant
     *         is allowed here. Delete the where() and every applicant sees
     *         everyone's applications. Any page called "my something" is
     *         protected by its query, not by its route.
     */
    public function mine(Request $request): Response
    {
        return Inertia::render('applications/mine', [
            'applications' => Application::with('job:id,title,location')
                ->where('candidate_email', $request->user()->email)
                ->latest()
                ->get(),
        ]);
    }

    /**
     * [EXAM] PSRS 2.IV.d - THE REVIEW SUMMARY. The paper names the five columns:
     *          i. Candidate Name  ii. Position Applied For  iii. Resume Score
     *          iv. Location Priority Points  v. Final Score
     *        They are rendered in that order in
     *        resources/js/pages/applications/review.tsx. Do not rename them -
     *        the examiner is looking for those exact words.
     */
    public function review(): Response
    {
        Gate::authorize('review', Job::class);

        return Inertia::render('applications/review', [
            // [LEARN] with('job') is EAGER LOADING. Without it, rendering 50
            //         rows would run 50 extra queries to fetch each job title
            //         (the N+1 problem again). With it: exactly 2 queries.
            'applications' => Application::with('job:id,title,location')
                ->orderByDesc('final_score')
                ->get(),
        ]);
    }

    /* ====================================================================
     | API - third-party application submission. routes/api.php
     ==================================================================== */

    /**
     * [EXAM] PSRS Part 1.e - a machine submitting an application.
     *
     * [LEARN] THE POINT OF THIS METHOD: it uses the SAME ScoringService as the
     *         web form above. A third-party integration and a human in a
     *         browser get identical scores, because the rules live in one class
     *         instead of being copy-pasted into two controllers.
     *         That is "separation of concern" in one sentence - say it out loud.
     */
    public function apiStore(Request $request)
    {
        $data = $request->validate([
            'job_id' => ['required', 'exists:job_listings,id'],
            'candidate_name' => ['required', 'string', 'max:255'],
            'candidate_email' => [
                'required',
                'email',
                Rule::unique('applications', 'candidate_email')
                    ->where(fn ($query) => $query->where('job_id', $request->integer('job_id'))),
            ],
            'resume_text' => ['required', 'string', 'min:10'],
            'has_disability' => ['nullable', 'boolean'],
        ]);

        $job = Job::findOrFail($data['job_id']);

        $scores = $this->scoring->evaluate(
            job: $job,
            resumeText: $data['resume_text'],
            hasDisability: $request->boolean('has_disability'),
        );

        $application = $job->applications()->create([
            ...$data,
            'has_disability' => $request->boolean('has_disability'),
            ...$scores,
        ]);

        // 201 Created is the correct status for "I made a new record".
        return response()->json(['data' => $application], 201);
    }
}
