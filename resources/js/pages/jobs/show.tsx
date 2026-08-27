import { Form, Head } from '@inertiajs/react';
import InputError from '@/components/input-error';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Checkbox } from '@/components/ui/checkbox';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Spinner } from '@/components/ui/spinner';
import jobs from '@/routes/jobs';

/*
|--------------------------------------------------------------------------
| [EXAM] PSRS Part 2.II - Candidate Application Handling
|--------------------------------------------------------------------------
| One job, and the form to apply for it.
*/

type Job = {
    id: number;
    title: string;
    department: string;
    location: string;
    salary: string;
};

type Props = {
    job: Job;
    alreadyApplied: boolean;
};

export default function Show({ job, alreadyApplied }: Props) {
    return (
        <>
            <Head title={job.title} />

            <div className="flex h-full flex-1 flex-col gap-6 rounded-xl p-4">
                <div>
                    <h1 className="text-2xl font-semibold">{job.title}</h1>
                    <div className="mt-2 flex flex-wrap items-center gap-2 text-sm text-muted-foreground">
                        <Badge variant="outline">{job.department}</Badge>
                        <Badge variant="outline">{job.location}</Badge>
                        <span className="tabular-nums">
                            Tsh. {new Intl.NumberFormat('en-TZ').format(Number(job.salary))} / month
                        </span>
                    </div>
                </div>

                {alreadyApplied ? (
                    /*
                      [EXAM] "Ensure each candidate can apply only once per job."
                      [LEARN] THREE LAYERS defend this rule, and you should say so:
                                1. this UI, which does not show the form at all
                                2. StoreApplicationRequest's Rule::unique
                                3. the unique index in the applications migration
                              UI can be bypassed. Validation can be bypassed by the
                              API. The database index cannot be bypassed by anything.
                    */
                    <div className="rounded-xl border border-green-600/30 bg-green-600/5 p-4 text-sm">
                        You have already applied for this job. Each candidate may apply
                        once per position.
                    </div>
                ) : (
                    <div className="max-w-lg rounded-xl border p-4">
                        <h2 className="mb-4 font-medium">Apply for this position</h2>

                        {/* [REUSE] Same <Form> pattern as auth/login.tsx and jobs/index.tsx.
                            Third time you have seen it - that is the point. */}
                        <Form
                            {...jobs.apply.form(job.id)}
                            resetOnSuccess
                            className="grid gap-4"
                        >
                            {({ processing, errors }) => (
                                <>
                                    <div className="grid gap-2">
                                        <Label htmlFor="candidate_name">Full name</Label>
                                        <Input
                                            id="candidate_name"
                                            name="candidate_name"
                                            required
                                            autoFocus
                                            placeholder="Ally Mdoka"
                                        />
                                        <InputError message={errors.candidate_name} />
                                    </div>

                                    <div className="grid gap-2">
                                        <Label htmlFor="candidate_email">Email</Label>
                                        <Input
                                            id="candidate_email"
                                            name="candidate_email"
                                            type="email"
                                            required
                                            placeholder="amdoka@example.com"
                                        />
                                        {/* This is where "You have already applied for this
                                            job." appears - the message we wrote in
                                            StoreApplicationRequest::messages(). */}
                                        <InputError message={errors.candidate_email} />
                                    </div>

                                    <div className="grid gap-2">
                                        <Label htmlFor="resume_text">
                                            Resume / brief profile summary
                                        </Label>
                                        {/* [LEARN] A plain <textarea>. There is no shadcn
                                            Textarea in this kit - check components/ui before
                                            importing something that does not exist. */}
                                        <textarea
                                            id="resume_text"
                                            name="resume_text"
                                            required
                                            rows={4}
                                            placeholder="Five years building Laravel and React systems..."
                                            className="rounded-md border border-input bg-transparent px-3 py-2 text-sm shadow-xs focus-visible:ring-[3px] focus-visible:outline-none"
                                        />
                                        <InputError message={errors.resume_text} />
                                    </div>

                                    {/*
                                      [EXAM] UTUMISHI - the disability priority input.

                                      [LEARN] ***value="1" IS LOAD-BEARING.*** A checkbox with
                                              no value submits the string "on" when ticked,
                                              and Laravel's `boolean` rule REJECTS "on". Leave
                                              it off and every candidate silently scores 0
                                              disability points - the exact requirement you
                                              were being marked on. Compare with
                                              auth/login.tsx, where <Checkbox name="remember" />
                                              needs no value because Laravel only checks
                                              whether the key is present.
                                    */}
                                    <div className="flex items-center gap-3">
                                        <Checkbox
                                            id="has_disability"
                                            name="has_disability"
                                            value="1"
                                        />
                                        <Label htmlFor="has_disability" className="font-normal">
                                            I am a person with a disability
                                        </Label>
                                    </div>
                                    <InputError message={errors.has_disability} />

                                    <div>
                                        <Button type="submit" disabled={processing}>
                                            {processing && <Spinner />}
                                            Submit application
                                        </Button>
                                    </div>
                                </>
                            )}
                        </Form>
                    </div>
                )}
            </div>
        </>
    );
}

Show.layout = {
    breadcrumbs: [{ title: 'Jobs', href: jobs.index() }],
};
