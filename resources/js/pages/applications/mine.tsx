import { Head, Link } from '@inertiajs/react';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import applications from '@/routes/applications';
import jobs from '@/routes/jobs';

/*
| A worked example of the five rules for adding a page:
|   1. This file's path matches Inertia::render('applications/mine') exactly.
|   2. The `applications` prop is the array key the controller sent.
|   3. No layout import - app.tsx gives every non-auth page AppLayout.
|   4. The route is named applications.mine, so it lands in @/routes/applications.
|   5. No role gate. Any applicant may open it; the controller's where()
|      clause is what stops them seeing anyone else's rows.
*/

type Row = {
    id: number;
    resume_score: number;
    location_priority: number;
    disability_priority: number;
    final_score: number;
    created_at: string;
    job: { id: number; title: string; location: string };
};

export default function Mine({ applications: rows }: { applications: Row[] }) {
    return (
        <>
            <Head title="My Applications" />

            <div className="flex h-full flex-1 flex-col gap-4 rounded-xl p-4">
                <div>
                    <h1 className="text-2xl font-semibold">My Applications</h1>
                    <p className="text-sm text-muted-foreground">
                        Everything you have applied for, newest first.
                    </p>
                </div>

                {rows.length === 0 ? (
                    <div className="rounded-xl border p-8 text-center">
                        <p className="text-muted-foreground">
                            You haven&apos;t applied for anything yet.
                        </p>
                        <Button asChild className="mt-4">
                            <Link href={jobs.index()}>Browse jobs</Link>
                        </Button>
                    </div>
                ) : (
                    <div className="grid gap-3">
                        {rows.map((row) => (
                            <div
                                key={row.id}
                                className="flex flex-wrap items-center justify-between gap-3 rounded-xl border p-4"
                            >
                                <div>
                                    <Link
                                        href={jobs.show(row.job.id)}
                                        className="font-medium hover:underline"
                                    >
                                        {row.job.title}
                                    </Link>
                                    <div className="mt-1 flex items-center gap-2">
                                        <Badge variant="outline">{row.job.location}</Badge>
                                        <span className="text-xs text-muted-foreground">
                                            {new Date(row.created_at).toLocaleDateString()}
                                        </span>
                                    </div>
                                </div>

                                <div className="text-right">
                                    <div className="text-2xl font-semibold tabular-nums">
                                        {row.final_score}
                                    </div>
                                    <div className="text-xs text-muted-foreground tabular-nums">
                                        {row.resume_score} resume · +{row.location_priority}{' '}
                                        location · +{row.disability_priority} priority
                                    </div>
                                </div>
                            </div>
                        ))}
                    </div>
                )}
            </div>
        </>
    );
}

Mine.layout = {
    breadcrumbs: [{ title: 'My Applications', href: applications.mine() }],
};
