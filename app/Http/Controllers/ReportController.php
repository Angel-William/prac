<?php

namespace App\Http\Controllers;

use App\Models\Application;
use App\Models\Job;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Inertia\Inertia;
use Inertia\Response;

/**
 * [EXAM] UTUMISHI - "Report & View". The second paper asks for a report the
 *        first one does not. This is the whole of that requirement.
 *
 * [LEARN] A LIST is not a REPORT. A report AGGREGATES: counts, averages,
 *         groupings, rankings. That is the difference the examiner is after.
 */
class ReportController extends Controller
{
    public function index(): Response
    {
        Gate::authorize('review', Job::class);

        return Inertia::render('reports/index', [
            'summary' => [
                'jobs' => Job::count(),
                'activeJobs' => Job::active()->count(),
                'applications' => Application::count(),
                'withDisability' => Application::where('has_disability', true)->count(),

                // [LEARN] avg() runs AVG() in SQL - the database does the maths,
                //         not PHP. Never pull 10,000 rows into memory to average
                //         a column. round(..., 1) keeps the tile readable.
                'averageScore' => round((float) Application::avg('final_score'), 1),
            ],

            // [LEARN] GROUP BY, expressed in Eloquent. DB::raw() lets you write
            //         a bare SQL fragment when the query builder has no method
            //         for it. Use it sparingly and never with user input.
            'byDepartment' => Job::select('department', DB::raw('count(*) as jobs'))
                ->groupBy('department')
                ->orderByDesc('jobs')
                ->get(),

            // The leaderboard: who should we interview first?
            'topCandidates' => Application::with('job:id,title')
                ->orderByDesc('final_score')
                ->limit(10)
                ->get(),
        ]);
    }
}
