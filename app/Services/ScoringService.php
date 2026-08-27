<?php

namespace App\Services;

use App\Models\Job;

/**
 * ============================================================================
 * THE MOST IMPORTANT FILE IN THIS PROJECT. Open it during your demo.
 * ============================================================================
 *
 * [EXAM] PSRS constraint - "Use classes or modules for separation of concern."
 *        This class IS your evidence. If this logic lived inside
 *        ApplicationController, you would lose those marks.
 *
 * [EXAM] PSRS Part 2.III + 2.IV - location priority, resume score, final score.
 * [EXAM] UTUMISHI              - disability priority.
 *
 * [LEARN] WHY A SERVICE CLASS?
 *         A controller's job is: take a request, hand it to something that
 *         knows the rules, return a response. It should NOT know that Remote
 *         is worth 3 points. Put business rules in a service and you get:
 *           - one place to change them
 *           - the same rules on the web form AND the API (see routes/api.php)
 *           - something you can unit test without a browser
 *             (see tests/Feature/ScoringServiceTest.php)
 */
class ScoringService
{
    /**
     * [EXAM] PSRS 2.IV.a - "Assign a score between 1 and 10 to evaluate the
     *        candidate's resume. This score can be mocked randomly or fixed
     *        for simulation process."
     *
     * The paper explicitly allows mocking, so we mock. In a real system this
     * is where you would call an AI service or a keyword matcher.
     *
     * [LEARN] random_int() not rand(): random_int is cryptographically secure
     *         and throws on failure instead of silently returning junk.
     */
    public function resumeScore(string $resumeText): int
    {
        return random_int(1, 10);
    }

    /**
     * [EXAM] PSRS 2.III + 2.IV.b - THE RULE THE EXAMINER WILL TEST:
     *
     *          Remote          -> +3
     *          DAR ES SALAAM   -> +2
     *          anywhere else   -> +1
     *
     * [LEARN] READ THE QUESTION TWICE. The paper says, in bold, that the points
     *         come from "the location of the job they are applying for (NOT the
     *         candidate's location)". That is a deliberate trap.
     *         This method takes a Job object rather than a plain string, so it
     *         is IMPOSSIBLE to pass the candidate's location by mistake. The
     *         type system enforces that you read the question correctly.
     *
     * [LEARN] match() is PHP 8's switch. It compares strictly (===) and has no
     *         fall-through. strtoupper + trim make the comparison forgiving of
     *         "remote", " Remote " and "REMOTE".
     */
    public function locationPriority(Job $job): int
    {
        return match (strtoupper(trim($job->location))) {
            'REMOTE' => 3,
            'DAR ES SALAAM' => 2,
            default => 1,
        };
    }

    /**
     * [EXAM] UTUMISHI - "hii mpya ilitaka priority iangaliwe kwa disability."
     *        The second paper moves priority from location to disability.
     *
     * We keep BOTH rules in this class. That way one codebase answers either
     * paper, and you can explain the difference out loud instead of rewriting
     * code under time pressure.
     */
    public function disabilityPriority(bool $hasDisability): int
    {
        return $hasDisability ? 3 : 0;
    }

    /**
     * [EXAM] PSRS 2.IV.c - "Final Score = Resume Quality Score + Location
     *        priority score" (+ disability priority, for the UTUMISHI paper).
     */
    public function finalScore(int $resumeScore, int $locationPriority, int $disabilityPriority = 0): int
    {
        return $resumeScore + $locationPriority + $disabilityPriority;
    }

    /**
     * THE ONE METHOD THE CONTROLLERS ACTUALLY CALL.
     *
     * [LEARN] Notice the return: an array whose keys are EXACTLY the column
     *         names on the applications table. That lets the controller write
     *
     *             $job->applications()->create([...$data, ...$scores]);
     *
     *         with no manual mapping at all. Designing a return value to match
     *         where it is going is a small trick that removes a lot of code.
     *
     * @return array{resume_score:int, location_priority:int, disability_priority:int, final_score:int}
     */
    public function evaluate(Job $job, string $resumeText, bool $hasDisability = false): array
    {
        $resume = $this->resumeScore($resumeText);
        $location = $this->locationPriority($job);
        $disability = $this->disabilityPriority($hasDisability);

        return [
            'resume_score' => $resume,
            'location_priority' => $location,
            'disability_priority' => $disability,
            'final_score' => $this->finalScore($resume, $location, $disability),
        ];
    }
}
