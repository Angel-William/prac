<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * [EXAM] PSRS Part 2.II - candidate submits Name, Email, Resume text.
 * [EXAM] PSRS constraint - "Ensure each candidate can apply only once per job."
 */
class StoreApplicationRequest extends FormRequest
{
    /**
     * Anyone logged in may apply - including viewers. Only JOB MANAGEMENT is
     * restricted, not applying. Re-read the paper: it never says otherwise.
     */
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'candidate_name' => ['required', 'string', 'max:255'],

            'candidate_email' => [
                'required',
                'email',

                // [EXAM] THE "APPLY ONLY ONCE PER JOB" RULE.
                //
                // [LEARN] Rule::unique('applications') alone would mean an email
                //         can only ever appear ONCE in the whole table - so a
                //         candidate could never apply for a second job. The
                //         ->where() narrows it to "unique WITHIN this job".
                //
                //         $this->route('job') grabs the Job model that Laravel
                //         already resolved from the /jobs/{job}/apply URL.
                Rule::unique('applications', 'candidate_email')
                    ->where(fn ($query) => $query->where('job_id', $this->route('job')->id)),
            ],

            'resume_text' => ['required', 'string', 'min:10'],

            // [LEARN] ***THE CHECKBOX TRAP - THIS ONE BREAKS THE SCORING.***
            //         An unchecked checkbox submits nothing (hence nullable).
            //         A checked one submits the string "on" UNLESS you give the
            //         input an explicit value="1" - and "on" FAILS the boolean
            //         rule. See resources/js/pages/jobs/show.tsx: the Checkbox
            //         there carries value="1" for exactly this reason.
            'has_disability' => ['nullable', 'boolean'],
        ];
    }

    /**
     * [LEARN] Custom messages. Without this the user sees "The candidate email
     *         has already been taken", which is confusing. Error messages are
     *         part of the product, and examiners notice.
     */
    public function messages(): array
    {
        return [
            'candidate_email.unique' => 'You have already applied for this job.',
            'resume_text.min' => 'Please write at least a sentence about yourself.',
        ];
    }
}
