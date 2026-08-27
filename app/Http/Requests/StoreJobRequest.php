<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

/**
 * [REUSE] You already generated app/Http/Requests/StoreVacancyRequest.php and
 *         left it empty. THIS is what that file was for. Compare them.
 *
 * [LEARN] A Form Request is validation moved OUT of the controller.
 *         You type-hint it in the controller method instead of Request:
 *
 *             public function store(StoreJobRequest $request)
 *
 *         Laravel then runs authorize() and rules() BEFORE your method body
 *         runs. If validation fails, the user is redirected back with errors
 *         and your controller is never even called.
 *
 * [LEARN] ***THE STUB TRAP*** php artisan make:request generates
 *         `return false;` in authorize(). Leave it and EVERY request 403s and
 *         you will lose ten minutes wondering why. Yours currently says false.
 *         Always change it.
 */
class StoreJobRequest extends FormRequest
{
    /**
     * [EXAM] PSRS Part 1.c - RBAC enforced a second time, at the request level.
     *
     * We already guard the route with 'role:admin,editor' middleware. Doing it
     * here too is defence in depth: if someone later re-registers this
     * controller on an unguarded route, the check still holds.
     */
    public function authorize(): bool
    {
        return $this->user()?->canManageJobs() ?? false;
    }

    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:255'],
            'department' => ['required', 'string', 'max:255'],
            'location' => ['required', 'string', 'max:255'],

            // [LEARN] The HTML form sends "1200000" as a STRING. 'numeric'
            //         accepts numeric strings; 'integer' would reject it.
            'salary' => ['required', 'numeric', 'min:0'],

            // [LEARN] A checkbox that is unchecked sends NOTHING at all, so the
            //         key is absent - hence 'nullable' rather than 'required'.
            'is_active' => ['nullable', 'boolean'],
        ];
    }
}
