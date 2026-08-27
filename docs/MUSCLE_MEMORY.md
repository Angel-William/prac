# Laravel Resource Muscle Memory

Your job portal, stripped of every comment, down to the eight shapes that
repeat. The codebase is for understanding. **This card is for reproducing** —
swap the noun and the shape holds.

Read `X` as whatever the paper calls it: Job, Product, Vacancy, Loan, Patient.

---

## If you remember nothing else

1. **Build in one direction:** migration → model → route → controller → page.
   Never jump ahead; each step needs the one before it.
2. **Every controller method is one or two lines.** If yours is long, the logic
   belongs in a service.
3. **Validation goes in a Form Request** — and its `authorize()` must be changed
   from `false`.
4. **Every React form is the same `<Form>` block** from `login.tsx`. Change the
   action and the field names.
5. **Seed before you demo.** Empty tables prove nothing.

---

## The eight shapes

Numbered in build order, because the order is what stops you getting stuck.

### 1 · Scaffold — four commands, 60 seconds

```bash
php artisan make:model X -m
php artisan make:controller XController --resource
php artisan make:request StoreXRequest
php artisan make:seeder XSeeder
```

`-m` makes the migration at the same time. `--resource` gives you all seven
methods pre-stubbed.

### 2 · Migration — columns, foreign key, constraint

```php
Schema::create('xs', function (Blueprint $table) {
    $table->id();
    $table->string('title');
    $table->decimal('amount', 12, 2);
    $table->boolean('is_active')->default(true);
    $table->foreignId('y_id')->constrained()->cascadeOnDelete();
    $table->timestamps();

    $table->unique(['y_id', 'email']);   // "only once per Y"
});
```

Money is `decimal`. Roles are `string`, never `enum`. A "can only do it once"
rule is a `unique` index.

### 3 · Model — fillable, casts, relationship, scope

```php
class X extends Model
{
    protected $fillable = ['title', 'amount', 'is_active', 'y_id'];

    protected function casts(): array
    {
        return ['is_active' => 'boolean'];
    }

    public function ys(): HasMany
    {
        return $this->hasMany(Y::class);
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}
```

Four things, always in this order. The inverse side is `belongsTo(X::class)`.

### 4 · Routes — one line per feature

```php
Route::middleware(['auth', 'verified'])->group(function () {
    Route::resource('xs', XController::class)->only(['index', 'show']);

    Route::post('xs/{x}/apply', [YController::class, 'store'])->name('xs.apply');

    Route::middleware('role:admin,editor')->group(function () {
        Route::resource('xs', XController::class)
            ->only(['store', 'update', 'destroy']);
    });
});
```

Splitting one resource across two groups is legal while the `only()` sets don't
overlap. That split **is** your RBAC answer.

### 5 · Form Request — validation out of the controller

```php
public function authorize(): bool
{
    return $this->user()?->canManageJobs() ?? false;
}

public function rules(): array
{
    return [
        'title' => ['required', 'string', 'max:255'],
        'amount' => ['required', 'numeric', 'min:0'],
        'is_active' => ['nullable', 'boolean'],
        'email' => [
            'required', 'email',
            Rule::unique('ys', 'email')
                ->where(fn ($q) => $q->where('x_id', $this->route('x')->id)),
        ],
    ];
}
```

> **TRAP** — the generator writes `return false;` in `authorize()`. Leave it and
> every request 403s.

### 6 · Controller — five methods, one or two lines each

```php
public function index()
{
    return Inertia::render('xs/index', [
        'xs' => X::withCount('ys')->latest()->paginate(10)->withQueryString(),
    ]);
}

public function store(StoreXRequest $request)
{
    X::create($request->validated());

    return to_route('xs.index')->with('success', 'Saved.');
}

public function show(X $x)
{
    return Inertia::render('xs/show', ['x' => $x]);
}

public function update(StoreXRequest $request, X $x)
{
    $x->update($request->validated());

    return to_route('xs.index');
}

public function destroy(X $x)
{
    $x->delete();

    return to_route('xs.index');
}
```

Type-hint `X $x` and Laravel fetches the record and 404s for you. `withCount`
avoids N+1. `withQueryString` keeps filters across pages.

### 7 · React page — the only form block you need

```jsx
import { Form, Head } from '@inertiajs/react';
import InputError from '@/components/input-error';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import xs from '@/routes/xs';

export default function Index({ xs: page }) {
    return (
        <>
            <Head title="Xs" />

            <Form {...xs.store.form()} resetOnSuccess className="grid gap-4">
                {({ processing, errors }) => (
                    <>
                        <Label htmlFor="title">Title</Label>
                        <Input id="title" name="title" required />
                        <InputError message={errors.title} />

                        <Button type="submit" disabled={processing}>Save</Button>
                    </>
                )}
            </Form>

            {page.data.map((x) => (
                <div key={x.id}>{x.title}</div>
            ))}
        </>
    );
}

Index.layout = { breadcrumbs: [{ title: 'Xs', href: xs.index() }] };
```

No `useState`, no `onChange`. Inputs bind by `name`. Editing uses `defaultValue`
plus a `key` so the inputs remount.

> **THREE TRAPS** — it's `xs.store.form()`, not `xs.store().form()`. A checkbox
> needs `value="1"` or it sends `"on"` and fails `boolean`. After `paginate()`
> the rows are `page.data`, not `page`.

### 8 · Seeder — last step before the demo

```php
User::factory()->create(['email' => 'admin@x.go.tz', 'role' => 'admin']);
User::factory()->create(['email' => 'viewer@x.go.tz', 'role' => 'viewer']);

X::create(['title' => 'A', 'location' => 'Remote']);          // +3
X::create(['title' => 'B', 'location' => 'DAR ES SALAAM']);   // +2
X::create(['title' => 'C', 'location' => 'Mwanza']);          // +1

// DatabaseSeeder.php — a seeder you never call does nothing
$this->call([XSeeder::class]);

// php artisan migrate:fresh --seed
```

Factory password is `password`. Pick rows that prove every branch of your logic
— one per rule.

---

## The two extras that carry the auth marks

### A · Middleware — guards a route

```php
// app/Http/Middleware/CheckRole.php
public function handle(Request $request, Closure $next, string ...$roles): Response
{
    if (! $request->user() || ! in_array($request->user()->role, $roles, true)) {
        abort(403);
    }

    return $next($request);
}

// bootstrap/app.php  — NOT Kernel.php, that file no longer exists
$middleware->alias([
    'role' => \App\Http\Middleware\CheckRole::class,
    'apikey' => \App\Http\Middleware\ApiKey::class,
]);
```

The API-key one is the same shape: compare `$request->header('X-API-Key')`
against `config('services.api_key')`, `Log::warning`, return 401 JSON.

### B · Service class — wherever the paper says "separation of concern"

```php
// app/Services/ScoringService.php
public function evaluate(X $x, string $text, bool $flag): array
{
    return [
        'a_score' => random_int(1, 10),
        'b_score' => $this->priority($x),
        'final_score' => /* the sum */,
    ];
}

// Controller — inject it, never `new` it
public function __construct(private ScoringService $scoring) {}

$x->ys()->create([...$data, ...$this->scoring->evaluate($x, ..., ...)]);
```

Return an array whose keys match your column names and the controller becomes
one line. Say out loud: *"the web form and the API score identically because the
rules live in one class."*

---

## What to build, and in what order

| Piece | Verdict | Cost |
|---|---|---|
| Migrations + models | **Core** | 6 min |
| Role column + CheckRole middleware | **Core** | 7 min |
| Service class with the scoring rules | **Core** | 6 min |
| Routes + two controllers | **Core** | 10 min |
| Index page with inline form | **Core** | 10 min |
| Review / summary table | **Core** | 6 min |
| Seeders | **Core** | 5 min |
| API key middleware + api.php | **Core** | 4 min |
| Form Requests | If time — inline `$request->validate()` otherwise | +4 min |
| Policy | If time — middleware alone passes | +5 min |
| Pagination + search | 3-hour paper only | +8 min |
| Reports page | 3-hour paper only | +15 min |
| Users & roles page | 3-hour paper only | +12 min |
| Flash toasts | Skip. Use the error text. | — |
| One scoring test | Worth it if 10 min spare | +4 min |

Core totals about **54 minutes** — the whole one-hour paper with six minutes for
the demo. Nothing in the optional list changes a single line of the core, so if
the clock beats you, what you have still works.

---

## Tomorrow, on paper

Not reading — **writing**. Close the laptop, use a notebook, check afterwards.
Anything you stall on is what to re-read tonight.

- [ ] The four scaffold commands, in order, without looking
- [ ] A migration with a foreign key and a two-column unique index
- [ ] A model with fillable, casts, one relationship and one scope
- [ ] A route file where two roles get different access to one resource
- [ ] `index` and `store` from memory, including `to_route`
- [ ] The `<Form>` block with one field and its `InputError`
- [ ] `CheckRole::handle` and where its alias is registered
- [ ] Name the five traps: `authorize`, checkbox `value`, `.store.form()`,
      `page.data`, no `Kernel.php`

Recognising code and producing it are different skills. Only the second one is
what the exam tests.
