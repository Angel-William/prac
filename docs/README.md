# Job Portal — documentation

Two documents. One is about **this codebase**; the other is about **your hands**.

| File | What it's for | When to read it |
|---|---|---|
| [JOB_PORTAL.md](JOB_PORTAL.md) | Requirement → file map for both papers, setup commands, seeded logins, the traps, and the demo script | Open during the exam when someone asks "where is X" |
| [MUSCLE_MEMORY.md](MUSCLE_MEMORY.md) | The eight repeating shapes with every comment stripped out, plus core-vs-optional triage and a written self-test | The night before — and write it out, don't just read it |
| [Job-Portal-Build-Order.html](Job-Portal-Build-Order.html) | Twelve steps from `laravel new` to the demo, every instruction verified against this project — plus an audit of five errors in the common version of this guide | Rebuilding from scratch, or checking a claim before you trust it |

Open the `.html` one in a browser (double-click it). It is self-contained.

The annotated source is the third document. Grep it:

```bash
grep -rn "\[EXAM\]"  app routes resources/js database   # requirement → line
grep -rn "\[LEARN\]" app routes resources/js            # patterns and traps
grep -rn "\[REUSE\]" app routes resources/js            # what the starter kit gave you
```
