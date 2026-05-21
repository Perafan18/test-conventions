# test-conventions

> Pest 4 + Laravel test conventions distributed as PHP-CS-Fixer custom fixers, behind a small `test-conventions` CLI. Canonical doc + Claude Code skill in the same repo.

[![License: MIT](https://img.shields.io/badge/License-MIT-yellow.svg)](#license)
[![PHP](https://img.shields.io/badge/PHP-8.2%2B-blue.svg)]()
[![Status](https://img.shields.io/badge/status-stable-green.svg)]()

📖 También disponible en [español](README.es.md).

## What this is

A standard for Pest 4 + Laravel test files, shipped as **three artifacts** versioned together:

1. **Canonical doc** — [`CONVENTIONS.md`](CONVENTIONS.md). The human-readable yardstick.
2. **Composer package + CLI** — `vendor/bin/test-conventions` with `check`, `fix`, `list-rules`, `init`. Wraps PHP-CS-Fixer custom fixers; the client never has to touch `php-cs-fixer` directly.
3. **Claude Code plugin** — skill that loads `CONVENTIONS.md` into context when an agent writes test files.

The three are released together via SemVer.

## Why this exists

If you maintain several Laravel/Pest projects, you tend to derive the same conventions doc in each one and copy-paste it across. The intersection is roughly 85% (filosofía, AAA, factories, mocking only at boundaries, anti-patterns); the remaining 15% is genuinely per-project (factory states, custom expectations, the comments policy your project picked). Over time the 85% drifts subtly between projects.

A common workaround is to put the mechanizable rules inside the test suite itself (e.g. `tests/Unit/ConventionsTest.php` that greps the rest of `tests/`). It works but it's categorically confused: that file is *lint*, not a test of the SUT. This package replaces the duplication with a single source of truth, and moves the enforcement to pre-commit + CI where it belongs.

## Quick start

```bash
composer require --dev perafan/test-conventions
vendor/bin/test-conventions init
```

`init` writes two small files:

- **`.php-cs-fixer.dist.php`** — a one-liner that delegates to the vendor's config. Editor plugins (PhpStorm, VSCode with PHP-CS-Fixer extensions) pick it up automatically and show violations inline.
- **`test-conventions.php`** — optional overrides (paths, allowlist, `partial_mock_comment_policy`, per-rule config).

That's it. There's no boilerplate config to maintain in your repo — it lives inside `vendor/perafan/test-conventions/`.

## Usage

### Commands

| Command | What it does |
|---|---|
| `vendor/bin/test-conventions check` | Report violations without modifying files. Exits 1 if any are found |
| `vendor/bin/test-conventions fix` | Apply autofixes (`should ` → strip, `toBe(true)` → `toBeTrue()`, etc.) |
| `vendor/bin/test-conventions list-rules` | Print a table of the 11 rules with section, mode (autofix/detect), description |
| `vendor/bin/test-conventions init` | Bootstrap the two client files |

Example output:

```
$ vendor/bin/test-conventions check
tests/Feature/UserTest.php:14: Perafan/test_conventions_max_description_length Description exceeds 50 chars (got 67): "..."
tests/Unit/PostTest.php:8: Perafan/test_conventions_forbidden_matchers Use toBeTrue() instead of toBe(true)

1 file has autofixable violations. Run `vendor/bin/test-conventions fix` to apply.
Found 2 violations across 2 files.
```

`file:line:` is clickable in modern editors and terminals.

### CI (GitHub Actions)

```yaml
- run: vendor/bin/pint --test
- run: vendor/bin/test-conventions check
```

Pint keeps handling the Laravel preset + built-ins. `test-conventions` handles only test-convention rules.

### Pre-commit (lefthook)

```yaml
pre-commit:
  commands:
    pint:
      glob: "*.php"
      run: vendor/bin/pint --test {staged_files}
    test-conventions:
      glob: "tests/**/*.php"
      run: vendor/bin/test-conventions check {staged_files}
```

### Editor inline

The `.php-cs-fixer.dist.php` that `init` generates delegates to the package config, so the PhpStorm and VSCode PHP-CS-Fixer plugins show violations inline. No extra setup.

### Why not `pint.json`

Pint v1.27 doesn't auto-discover third-party PHP-CS-Fixer custom fixers from `pint.json` (verified empirically — it fails with `unknown fixers`). The `test-conventions` binary works around this by invoking PHP-CS-Fixer internally with the fixers properly registered. From the client's perspective there's a single, named CLI to run; the PHP-CS-Fixer plumbing stays inside the package. If Pint adds support upstream, `init` could switch to writing a `pint.json` entry instead.

## Rules

| ID | Name | Mode | Section |
|---|---|---|---|
| R01 | `it()` instead of `test()` top-level | autofix | §2.1 |
| R02 | Description ≤ 50 chars | detect | §2.2 |
| R03 | No `should ` / `it tests ` / `tests that ` prefix | autofix (strip) | §2.2 |
| R04 | No `toBe(true\|false\|null)` — use semantic matchers | autofix (rewrite) | §4.2 |
| R05 | No `assertTrue(true)` / `expect(true)->toBeTrue()` | detect | §8.3 |
| R06 | No mocking `App\…` (configurable namespaces) | detect | §5.1 |
| R07 | No `->pause()` / `->wait()` with fixed timeouts in Browser tests | detect | §7.3 |
| R08 | No `sleep()` / `usleep()` in tests | detect | §8.5 |
| R09 | No `->only()` reaching main | autofix (strip) | §8.12 |
| R11 | No `/Users/` / `/home/` absolute paths in tests | detect | §8.13 |
| §5.3 | `partial_mock_comment_policy`: `forbid` / `require` / `allow` | detect | §5.3 |

**Code-review-only** (NOT mechanized — patterns infeasible to express cleanly over Tokens; revisit later if real pain emerges):

- R10 — no `try/catch` in test bodies (requires scope tracking to distinguish from `Http::fake([...])` callbacks)
- R12 — inserts in key tables go through helpers (requires method-chain analysis)

Read [`CONVENTIONS.md`](CONVENTIONS.md) for the full doc.

## Claude Code plugin

For agents (Claude Code) writing or editing tests:

```
/plugin marketplace add github:Perafan18/test-conventions
/plugin install test-conventions
```

The skill loads `CONVENTIONS.md` into context whenever an agent edits or writes test files in a project that has `perafan/test-conventions` installed. Suggests `vendor/bin/test-conventions check` after writing.

## Configuration (`test-conventions.php`)

```php
<?php

return [
    'paths' => ['tests'],

    'allowlist' => [
        // Substrings or paths to skip from the Finder.
        // 'Unit/ArchTest.php',
    ],

    'rules' => [
        // Override default rule configurations. Only include what you change.
        // 'Perafan/test_conventions_max_description_length' => ['limit' => 50],
        // 'Perafan/test_conventions_partial_mock_comment'   => ['policy' => 'forbid'],
    ],
];
```

## Architecture notes

- **Canonical/local split**: ~85% of every project's conventions doc is portable (filosofía, structure, mocking principles, anti-patterns). That lives in `CONVENTIONS.md`. The other ~15% is per-project (factory states, custom expectations, exact commands, coverage threshold) and lives in each consumer's own docs.
- **Conflict resolution via config, not forks**: when two real projects disagree (e.g. §5.3 partial mock comments — one project's policy gains, another's loses), the rule stays in the package and the posture is per-project in `test-conventions.php`. No bifurcation of the canonical doc.
- **`throw` vs collector**: when fixers `throw RuntimeException` to report a violation, PHP-CS-Fixer treats the file as errored and skips remaining fixers on it. The CLI sets an env var that activates a file-based `ViolationCollector` inside fixers — every violation across every rule and every file is surfaced in a single `check`. Backward-compatible: without the env var, fixers fall back to `throw`, so clients still using a manually-authored `.php-cs-fixer.dist.php` from older versions keep working.

## Contributing

Stable. Contributions welcome via Issues and PRs.

The package follows its own standard — the suite must pass `vendor/bin/test-conventions check` (dogfooded against `src/Tokens/` and `tests/`) before any merge.

## License

[MIT](LICENSE). Copyright © Pedro Perafan.
