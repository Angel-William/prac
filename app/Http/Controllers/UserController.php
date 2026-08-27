<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

/**
 * ============================================================================
 * [EXAM] PSRS Part 1.c - "Users can have roles such as admin, editor, viewer"
 * ============================================================================
 *
 * [LEARN] Having a `role` column is only HALF the requirement. Somebody has to
 *         be able to ASSIGN it. Without this screen the only way to create an
 *         admin is `php artisan tinker` - which you cannot do mid-demo with an
 *         examiner watching.
 *
 * [LEARN] WHO CAN REACH THIS: admins only. Note in routes/web.php that this
 *         sits in a 'role:admin' group, NOT 'role:admin,editor'.
 *         Editors manage jobs. Only an admin manages people. Two different
 *         guards on two different groups is what RBAC actually looks like.
 */
class UserController extends Controller
{
    public function index(Request $request): Response
    {
        return Inertia::render('users/index', [
            // [LEARN] Ask for exactly the columns the page needs. $hidden would
            //         already strip the password hash and 2FA secrets, but
            //         selecting explicitly is the habit worth building.
            'users' => User::select('id', 'name', 'email', 'role', 'email_verified_at')
                ->withCount('applications')
                ->orderBy('name')
                ->get(),

            // Lets the UI disable the current user's own dropdown.
            'currentUserId' => $request->user()->id,
        ]);
    }

    /**
     * [EXAM] PSRS 1.c - promote or demote a user.
     */
    public function updateRole(Request $request, User $user)
    {
        $data = $request->validate([
            // [LEARN] THIS is why the migration used string() and not enum().
            //         The allowed list lives here in PHP where it is one line to
            //         change, instead of being frozen into the database schema.
            'role' => ['required', 'in:admin,editor,viewer'],
        ]);

        /*
        | [LEARN] ***THE FOOTGUN GUARD.***
        |         Without this, an admin can set their OWN role to viewer, lose
        |         access to this page in the same request, and have no way back
        |         in except tinker. Any screen that can remove your own
        |         permissions needs a check like this one.
        |         Examiners notice when you have thought about it.
        */
        if ($user->id === $request->user()->id) {
            return back()->with('error', 'You cannot change your own role.');
        }

        $user->update($data);

        return back()->with('success', "{$user->name} is now {$user->role}.");
    }
}
