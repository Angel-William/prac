# Job Portal — PSRS + UTUMISHI in one codebase

One system that answers **both** practical papers:

- **PSRS** — auth, RBAC, API key, job listings, applications, **location** priority, review summary
- **UTUMISHI** — the same portal, but priority by **disability**, plus a Report & View page

Both scoring rules live side by side, so you can demo either paper without rewriting anything.

---

## Comment legend — grep for these

Every file is annotated. Search the project for the tag you need:

| Tag | Means |
|---|---|
| `[EXAM]` | This exact line answers a numbered requirement in the paper |
| `[LEARN]` | A pattern or trap worth understanding, not just copying |
| `[REUSE]` | Copied from something the starter kit already gave you — and where from |

```bash
grep -rn "\[EXAM\]" app routes resources/js database   # every requirement, mapped
grep -rn "\[LEARN\]" app routes resources/js           # every teaching note
grep -rn "\[REUSE\]" app routes resources/js           # what you didn't have to write
```

---

## Run it

```bash
php artisan config:clear          # you added API_KEY to .env
php artisan migrate:fresh --seed  # builds job_listings, applications, users.role
php artisan test --filter=Scoring # 6 assertions on the scoring rules
npm run dev                       # keep running: it regenerates Wayfinder routes
php artisan serve
```

### Logins (password is `password` for all three)

| Email | Role | Can do |
|---|---|---|
| `admin@psrs.go.tz` | admin | Everything, including deleting a job with applicants |
| `editor@psrs.go.tz` | editor | Post and edit jobs; **cannot** delete one with applicants |
| `viewer@psrs.go.tz` | viewer | Browse and apply only — no Review, no Reports, 403 on manage routes |

Anyone who registers at `/register` becomes a **viewer** (an applicant). To make
someone an admin or editor, log in as an admin and use the **Users** page.

### Signing in vs registering

| I want to be… | How |
|---|---|
| **An applicant** | Go to `/register` and sign up normally. Everyone who registers gets `role = 'viewer'` — the DB column default. A viewer browses jobs and applies. That *is* the applicant role; there is no separate signup. |
| **An admin/editor** | You cannot self-promote. Log in as `admin@psrs.go.tz` / `password`, open **Users** in the sidebar, and set anyone's role from the dropdown. |

**The chicken-and-egg:** the first admin can't be made in the UI, because you
must already be an admin to open that page. It comes from the seeder. Every
real system works this way — seed the first admin, manage the rest through the
app.

If a registered account gets bounced to `/verify-email` and can't reach
anything, that's the `verified` middleware in `routes/web.php` doing its job —
the account never confirmed its email. The **Users** page flags those with an
`unverified` badge.

---

## Requirement → file map

Take this into the exam. When the examiner asks "where is X", you point.

### PSRS Part 1 — Authorization and Authentication

| Requirement | Where | Note |
|---|---|---|
| 1.a Registration, login, **bcrypt** hashing | `app/Models/User.php` → `casts()` → `'password' => 'hashed'` | Came with the kit. Point at this line. |
| 1.b Password reset | Fortify — `/forgot-password` | Already built. Just open it. |
| 1.c RBAC — enforcement | `CheckRole.php`, `JobPolicy.php`, `role` column, `routes/web.php` | Middleware guards routes, policy guards records |
| 1.c RBAC — **assignment** | `UserController.php`, `users/index.tsx` | Admin-only. `role:admin`, not `role:admin,editor` |
| 1.d Session management | `.env` `SESSION_LIFETIME=30`, `SESSION_DRIVER=database`, logout in the user menu | |
| 1.e API key auth | `app/Http/Middleware/ApiKey.php`, `routes/api.php`, `config/services.php` | Includes the `Log::warning` the paper asks for |

### PSRS Part 2 — Job Recruitment System

| Requirement | Where |
|---|---|
| I. Job listings management | `JobController`, `jobs/index.tsx` (list + inline create/edit) |
| Pagination and search | `JobController@index` — `paginate(5)->withQueryString()`, grouped `orWhere` |
| II. Candidate applications | `ApplicationController@store`, `jobs/show.tsx` |
| III. Location-based priority | `ScoringService::locationPriority()` — takes a `Job`, never a string |
| IV.a Resume quality score | `ScoringService::resumeScore()` — mocked, as the paper allows |
| IV.b Location priority score | same method as III |
| IV.c Final score | `ScoringService::finalScore()` |
| IV.d Review summary | `applications/review.tsx` — the five column headings, word for word |
| Separation of concern | `app/Services/ScoringService.php` — **open this during the demo** |
| Store in database | 3 migrations |
| Apply only once per job | Unique index + `Rule::unique` + the UI — three layers |

### UTUMISHI

| Requirement | Where |
|---|---|
| Disability priority | `ScoringService::disabilityPriority()` + the `value="1"` checkbox in `jobs/show.tsx` |
| Report & View | `ReportController`, `reports/index.tsx` |
| Session management + RBAC | Same as PSRS |

---

## The traps that cost people the exam

1. **`jobs` is taken.** Laravel ships a `jobs` table for the queue (and your
   `.env` has `QUEUE_CONNECTION=database`). The model is `Job`, the table is
   `job_listings` — one line, `protected $table`, in `app/Models/Job.php`.

2. **`constrained()` can't guess it.** Because the table isn't `jobs`, the
   foreign key must say `constrained('job_listings')`. Miss it and the
   migration dies with "no such table: jobs".

3. **A checkbox sends `"on"`, not `true`.** `<Checkbox value="1" />` in
   `jobs/show.tsx` is load-bearing. Without it every candidate silently scores
   0 disability points — the exact rule you're being marked on.

4. **`make:request` generates `authorize(): false`.** Leave it and every
   request 403s. Your `StoreVacancyRequest` still says `false` — go look.

5. **There is no `app/Http/Kernel.php`.** Middleware aliases go in
   `bootstrap/app.php`. Any tutorial saying otherwise is for Laravel 10.

6a. **`withQueryString()` on a paginator.** Without it, clicking "page 2"
   drops `?search=` and page 2 silently shows unfiltered results.

6b. **Grouping `orWhere`.** A search filter written as
   `->where(...)->orWhere(...)` leaks out and ORs against every other
   condition — `WHERE is_active = 1 AND title LIKE ? OR department LIKE ?`
   quietly returns inactive rows. Wrap them in a nested closure.

7. **`usePage()` only works inside the Inertia page tree.** In `app.tsx`,
   `<Toaster />` is a *sibling* of `{app}`, not a child — so any hook calling
   `usePage()` from inside `components/ui/sonner.tsx` throws
   *"usePage must be used within the Inertia component"*. `useFlashToast()` is
   called once from `layouts/app-layout.tsx`, which **is** inside the tree.
   Never call it from both a layout and a page — you'd get every toast twice.

---

## Demo script — six minutes, in this order

1. Log in as **viewer**. No Review or Reports in the sidebar. Now type
   `/reports` in the address bar → **403**. RBAC proven, not claimed.
2. Log in as **admin**. Add a job in Dodoma with the inline form.
3. Open the Remote job → apply as Ally Mdoka, tick the disability box.
4. Apply again with the same email → *"You have already applied for this job."*
5. Open **Review** → the five columns, sorted by final score. Point out that
   Remote gave +3, and the disability box gave another +3.
5b. Open **Users** → promote the viewer to editor, log in as them, show that
   Review and Reports have appeared. Roles are data, not hard-coded.
6. Open **Reports** → counts, average, grouping, ranking. That's UTUMISHI's
   "Report & View".
7. Two curls:

```bash
curl -i http://localhost:8000/api/jobs
curl -i -H "X-API-Key: psrs-secret-key-2026" http://localhost:8000/api/jobs
```

401, then 200. Then open `ScoringService.php` and say the sentence:
*"The web form and the API score identically because the rules live in one
class, not copy-pasted into two controllers."*

---

## Notes

- `DB_CONNECTION=sqlite`. Both papers name PostgreSQL or MySQL. SQLite is
  relational and everything here is portable, but if the examiner is strict,
  switch `.env` to MySQL and re-run `migrate:fresh --seed`. Nothing else changes.
- Your `Employee`, `Product` and `Vacancy` resources are untouched and still
  routed. Delete them (routes, controllers, models, pages) once you're happy.
- Files changed rather than added were backed up first to
  `_backup_before_jobportal/`. Delete that folder before you submit.
