---
name: test-conventions
description: Load Pest 4 + Laravel test conventions when writing or editing test files in a project that uses perafan/test-conventions. Activates on tests/**/*.php paths and Pest keywords.
trigger:
  file_patterns:
    - "tests/**/*.php"
    - "**/Tests/**/*.php"
  keywords:
    - "pest"
    - "it()"
    - "expect()"
    - "test()"
    - "Pest"
    - "factory"
    - "RefreshDatabase"
    - "Http::fake"
    - "Storage::fake"
  dependency: "perafan/test-conventions"
---

# Test Conventions Skill

Activate this skill when the agent is writing, editing, or discussing test files in a project that has `perafan/test-conventions` in `composer.json`.

## What to do

1. **Read `vendor/perafan/test-conventions/CONVENTIONS.md`** and load it as context. This is the canonical document.
2. **Look for the project-local appendix**:
   - First check `docs/test-conventions.md` (if the project commits it to the repo).
   - If not found, ask the user where the local appendix lives (typically in a separate Obsidian vault or knowledge base).
3. **Apply the canonical rules first**, then the project appendix (which overrides specifics for the local domain).
4. **Before writing any test code**, mentally walk through the mechanical checklist:
   - Description in English, ≤ 50 chars
   - No `should ` / `it tests ` / `tests that ` prefix
   - Use `it()`, not `test()` (except for arch tests)
   - AAA blocks visible (Arrange / Act / Assert)
   - Mock only at boundaries (`Http::fake`, `Storage::fake`, etc.)
   - Never mock code in the project's `App\` namespace
   - Use semantic matchers (`toBeTrue()` over `toBe(true)`)
   - Avoid `->only()`, `->pause()`, `sleep()` in tests
5. **After writing tests**, suggest running:
   ```
   vendor/bin/test-conventions check tests/path/to/new/test.php
   ```
   to verify against the linter. For autofixable violations (`should ` prefix, `toBe(true)`, `->only()`, top-level `test('...')`), run `vendor/bin/test-conventions fix` to apply fixes automatically.

   > Note: the package distributes PHP-CS-Fixer custom fixers but exposes them through the `test-conventions` binary so the client never sees `php-cs-fixer` plumbing. Pint v1.27 does not auto-discover third-party custom fixers; the binary works around that internally.

## What NOT to do

- Do not invent factory states, custom expectations, or helper functions not declared in the project. If unsure, ask the user or check `tests/Pest.php`.
- Do not silently fix violations the agent introduced — surface them so the user can decide.
- Do not skip reading `CONVENTIONS.md` even if "you remember" the rules from a previous session. Read it.
- Do not write tests in any language other than English for descriptions, regardless of the project's UI/domain language.

## When the conventions don't cover a case

If the user asks for behavior the conventions don't address:

- Propose updating `CONVENTIONS.md` via PR to `perafan/test-conventions`.
- Document the exception inline (this is one of the few cases where a comment in a test is valid, depending on the project's `partial_mock_comment_policy` setting in `.php-cs-fixer.dist.php`).
- Do not improvise silently.

## Configuration awareness

The project's `.php-cs-fixer.dist.php` may configure rule-specific options. Common overrides to check:

- `Perafan/test_conventions_max_description_length` — may differ from default 50
- `Perafan/test_conventions_partial_mock_comment.policy` — `forbid` / `require` / `allow` (legitimate disagreement between projects)
- Rules disabled (omitted from the `setRules` array, or set to `false`) — respect what the project chose to skip
- `Finder` allowlists (`notPath()`) — pre-existing violations that have not yet been fixed; do not introduce new code into those files that breaks the rule

Read this config before writing tests in a project, especially when you're new to it.

## Rules that are code-review-only

Two rules in `CONVENTIONS.md` are documented but NOT mechanized by the Pint fixers (as of v1.0):

- **R10 — No `try/catch` in test bodies.** Hard to distinguish "test body" from "callback of `Http::fake([...])`" in Tokens. Catch in review.
- **R12 — Inserts in key tables (e.g., `reactions`, `article_views`) must go through helpers.** Requires method chain analysis that is awkward in Tokens. Catch in review.

When writing tests, apply these rules even though no linter will catch them.
