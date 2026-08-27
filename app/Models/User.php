<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Carbon;
use Laravel\Fortify\Contracts\PasskeyUser;
use Laravel\Fortify\PasskeyAuthenticatable;
use Laravel\Fortify\TwoFactorAuthenticatable;

/**
 * @property int $id
 * @property string $name
 * @property string $email
 * @property string $role
 * @property Carbon|null $email_verified_at
 * @property string $password
 * @property string|null $two_factor_secret
 * @property string|null $two_factor_recovery_codes
 * @property Carbon|null $two_factor_confirmed_at
 * @property string|null $remember_token
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
// [LEARN] This starter kit declares fillable/hidden with PHP ATTRIBUTES
//         (#[Fillable]) instead of the older `protected $fillable = []`.
//         Same meaning, newer syntax. We only added 'role' to the list.
#[Fillable(['name', 'email', 'password', 'role'])]
#[Hidden(['password', 'two_factor_secret', 'two_factor_recovery_codes', 'remember_token'])]
class User extends Authenticatable implements MustVerifyEmail
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable, PasskeyAuthenticatable, TwoFactorAuthenticatable;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',

            // [EXAM] PSRS Part 1.a - "stores user credentials using password
            //        hashing (e.g. bcrypt)". THIS LINE IS THE ANSWER. It came
            //        with the starter kit. Every time a password is assigned,
            //        Laravel hashes it with bcrypt automatically. You never
            //        write Hash::make() yourself, and a plain-text password
            //        can never reach the database.
            //        Point at this line in the demo.
            'password' => 'hashed',

            'two_factor_confirmed_at' => 'datetime',
        ];
    }

    /* ====================================================================
     | EVERYTHING BELOW THIS LINE IS NEW - added for the job portal
     ==================================================================== */

    /**
     * [EXAM] PSRS Part 1.c - Role-Based Access Control.
     *
     * [LEARN] Put role QUESTIONS on the model, not scattered through your
     *         controllers. If the rule ever changes ("managers can edit too")
     *         you change it here once, and middleware, policy and React all
     *         follow.
     */
    public function hasRole(string ...$roles): bool
    {
        return in_array($this->role, $roles, true);
    }

    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }

    /**
     * Admins and editors may post, edit and delete jobs. Viewers may only look.
     * Used by App\Policies\JobPolicy and by the React sidebar.
     */
    public function canManageJobs(): bool
    {
        return $this->hasRole('admin', 'editor');
    }

    public function applications(): HasMany
    {
        return $this->hasMany(Application::class);
    }
}
