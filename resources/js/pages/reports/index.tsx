import { Head } from '@inertiajs/react';
import reports from '@/routes/reports';

/*
|--------------------------------------------------------------------------
| [EXAM] UTUMISHI - "Report & View"
|--------------------------------------------------------------------------
| [LEARN] A LIST is not a REPORT. The review page lists rows; this page
|         AGGREGATES - counts, an average, a grouping and a ranking. That
|         distinction is the requirement. Built by ReportController.
*/

type Props = {
    summary: {
        jobs: number;
        activeJobs: number;
        applications: number;
        withDisability: number;
        averageScore: number;
    };
    byDepartment: { department: string; jobs: number }[];
    topCandidates: {
        id: number;
        candidate_name: string;
        final_score: number;
        job: { id: number; title: string };
    }[];
};

export default function Index({ summary, byDepartment, topCandidates }: Props) {
    // [LEARN] Build the tiles from an array instead of writing five near-identical
    //         blocks of JSX. If a sixth metric is added you touch one line.
    const tiles = [
        { label: 'Jobs posted', value: summary.jobs },
        { label: 'Active jobs', value: summary.activeJobs },
        { label: 'Applications', value: summary.applications },
        { label: 'With disability', value: summary.withDisability },
        { label: 'Average score', value: summary.averageScore },
    ];

    return (
        <>
            <Head title="Reports" />

            <div className="flex h-full flex-1 flex-col gap-6 rounded-xl p-4">
                <h1 className="text-2xl font-semibold">Recruitment Report</h1>

                <div className="grid gap-3 sm:grid-cols-2 lg:grid-cols-5">
                    {tiles.map((tile) => (
                        <div key={tile.label} className="rounded-xl border p-4">
                            <p className="text-sm text-muted-foreground">{tile.label}</p>
                            <p className="mt-1 text-3xl font-semibold tabular-nums">
                                {tile.value}
                            </p>
                        </div>
                    ))}
                </div>

                <div className="grid gap-6 lg:grid-cols-2">
                    <div className="rounded-xl border p-4">
                        <h2 className="mb-3 font-medium">Jobs by department</h2>
                        <ul className="space-y-2 text-sm">
                            {byDepartment.map((row) => (
                                <li
                                    key={row.department}
                                    className="flex items-center justify-between border-b pb-2 last:border-0"
                                >
                                    <span>{row.department}</span>
                                    <span className="tabular-nums text-muted-foreground">
                                        {row.jobs}
                                    </span>
                                </li>
                            ))}
                            {byDepartment.length === 0 && (
                                <li className="text-muted-foreground">No jobs yet.</li>
                            )}
                        </ul>
                    </div>

                    <div className="rounded-xl border p-4">
                        <h2 className="mb-3 font-medium">Top candidates</h2>
                        <ol className="space-y-2 text-sm">
                            {topCandidates.map((candidate, position) => (
                                <li
                                    key={candidate.id}
                                    className="flex items-center justify-between border-b pb-2 last:border-0"
                                >
                                    <span>
                                        <span className="mr-2 text-muted-foreground tabular-nums">
                                            {position + 1}.
                                        </span>
                                        {candidate.candidate_name}
                                        <span className="ml-2 text-muted-foreground">
                                            {candidate.job.title}
                                        </span>
                                    </span>
                                    <span className="font-semibold tabular-nums">
                                        {candidate.final_score}
                                    </span>
                                </li>
                            ))}
                            {topCandidates.length === 0 && (
                                <li className="text-muted-foreground">No applications yet.</li>
                            )}
                        </ol>
                    </div>
                </div>
            </div>
        </>
    );
}

Index.layout = {
    breadcrumbs: [{ title: 'Reports', href: reports.index() }],
};
