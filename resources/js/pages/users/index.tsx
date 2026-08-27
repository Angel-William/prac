import { Head, router } from '@inertiajs/react';
import { Badge } from '@/components/ui/badge';
import users from '@/routes/users';

/*
|==========================================================================
| [EXAM] PSRS Part 1.c - Role-Based Access Control: assigning the roles.
|==========================================================================
| [LEARN] This page is the ANSWER to "how does anyone become an admin?".
|         Admin only - see the 'role:admin' group in routes/web.php.
|
| [LEARN] THE CHICKEN-AND-EGG: the very first admin cannot be made here,
|         because you must already be an admin to open this page. That first
|         one comes from the SEEDER (database/seeders/JobPortalSeeder.php).
|         Every real system solves it the same way: seed the first admin,
|         then manage the rest through the UI.
*/

type Row = {
    id: number;
    name: string;
    email: string;
    role: 'admin' | 'editor' | 'viewer';
    email_verified_at: string | null;
    applications_count: number;
};

type Props = {
    users: Row[];
    currentUserId: number;
};

const ROLES = ['admin', 'editor', 'viewer'] as const;

// What each role may actually do - shown in the UI so the rules are visible
// to the examiner without them reading your middleware.
const ROLE_HELP: Record<string, string> = {
    admin: 'Everything, including managing these roles',
    editor: 'Post and edit jobs, read the review and reports',
    viewer: 'Browse jobs and apply — an applicant',
};

export default function Index({ users: list, currentUserId }: Props) {
    /*
      [LEARN] router.patch() is the imperative version of submitting a form.
              We use it here instead of <Form> because the "form" is a single
              <select> with no submit button - changing the value IS the intent.

              Wayfinder gives us the URL: users.role.url(id) -> /users/3/role
              Inertia sends a real PATCH; no ?_method spoofing needed, because
              this is an XHR from JavaScript rather than a browser form post.
    */
    const changeRole = (userId: number, role: string) => {
        router.patch(users.role.url(userId), { role }, { preserveScroll: true });
    };

    return (
        <>
            <Head title="Users & Roles" />

            <div className="flex h-full flex-1 flex-col gap-4 overflow-x-auto rounded-xl p-4">
                <div>
                    <h1 className="text-2xl font-semibold">Users &amp; Roles</h1>
                    <p className="text-sm text-muted-foreground">
                        Anyone who registers starts as a <strong>viewer</strong> (an
                        applicant). Promote them here.
                    </p>
                </div>

                <div className="overflow-x-auto rounded-xl border">
                    <table className="w-full text-sm">
                        <thead className="bg-muted/50 text-left text-muted-foreground">
                            <tr>
                                <th className="p-3 font-medium">Name</th>
                                <th className="p-3 font-medium">Email</th>
                                <th className="p-3 font-medium">Applications</th>
                                <th className="p-3 font-medium">Role</th>
                                <th className="p-3 font-medium">Can do</th>
                            </tr>
                        </thead>
                        <tbody>
                            {list.map((user) => {
                                const isSelf = user.id === currentUserId;

                                return (
                                    <tr key={user.id} className="border-t">
                                        <td className="p-3 font-medium">
                                            {user.name}
                                            {isSelf && (
                                                <Badge variant="secondary" className="ml-2">
                                                    you
                                                </Badge>
                                            )}
                                        </td>

                                        <td className="p-3">
                                            {user.email}
                                            {/* [LEARN] The 'verified' middleware in
                                                routes/web.php blocks unverified users from
                                                every page in the group. Surfacing it here
                                                turns a mystery redirect into an explanation. */}
                                            {!user.email_verified_at && (
                                                <Badge variant="destructive" className="ml-2">
                                                    unverified
                                                </Badge>
                                            )}
                                        </td>

                                        <td className="p-3 tabular-nums">
                                            {user.applications_count}
                                        </td>

                                        <td className="p-3">
                                            {/*
                                              [LEARN] Disabled for yourself. The server refuses
                                                      it too (UserController::updateRole) - this
                                                      is only the polite half. NEVER let the UI
                                                      be the only thing enforcing a rule.
                                            */}
                                            <select
                                                value={user.role}
                                                disabled={isSelf}
                                                onChange={(event) =>
                                                    changeRole(user.id, event.target.value)
                                                }
                                                className="h-9 rounded-md border border-input bg-transparent px-3 text-sm disabled:opacity-50"
                                            >
                                                {ROLES.map((role) => (
                                                    <option key={role} value={role}>
                                                        {role}
                                                    </option>
                                                ))}
                                            </select>
                                        </td>

                                        <td className="p-3 text-muted-foreground">
                                            {ROLE_HELP[user.role]}
                                        </td>
                                    </tr>
                                );
                            })}
                        </tbody>
                    </table>
                </div>
            </div>
        </>
    );
}

Index.layout = {
    breadcrumbs: [{ title: 'Users', href: users.index() }],
};
