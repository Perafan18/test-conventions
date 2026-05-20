# Changelog

Todos los cambios notables a este proyecto se documentan aca.

Formato basado en [Keep a Changelog](https://keepachangelog.com/en/1.1.0/) y [SemVer](https://semver.org/spec/v2.0.0.html).

## [Unreleased]

## [1.0.0] - 2026-05-19

### Stable release

The package is now adopted by two real client projects, validating the
architecture end-to-end:

- 11 mechanized fixers covering R01-R11 + the §5.3 `partial_mock_comment_policy`.
- The config policy resolves the §5.3 conflict between projects without
  forking the canonical doc.
- R10 and R12 documented as code-review-only — their patterns are
  inviable cleanly on Tokens; if real pain emerges in review, a future
  release implements them with ScopeTracker.

Clients adopt via `.php-cs-fixer.dist.php` (Pint v1.27 does not yet
auto-discover third-party custom fixers from `pint.json`; the package
distributes PHP-CS-Fixer custom fixers, clients register them explicitly).

### SKILL.md polished

- Suggested command updated from `vendor/bin/pint --test` to
  `vendor/bin/php-cs-fixer fix --dry-run`.
- Configuration awareness section now points at `.php-cs-fixer.dist.php`
  with a note about `notPath()` allowlists (pre-existing violations).

## [0.3.1] - 2026-05-19

### Fixed

- R01 no longer renames Pest's `test()` helper (the no-arg form returning
  the current TestCase). The fixer now requires the first meaningful
  token inside the parens to be a string literal.

## [0.3.0] - 2026-05-19

### Added — 7 fixers nuevos (paquete completo)

- `Perafan/test_conventions_it_not_test` — `it()` siempre, `test()` solo en arch tests (autofix: rename).
- `Perafan/test_conventions_no_assert_true_true` — sin `assertTrue(true)` ni `expect(true)->toBeTrue()`.
- `Perafan/test_conventions_no_pause_browser` — sin `->pause()` ni `->wait()` con timeout fijo en `tests/Browser/`.
- `Perafan/test_conventions_no_sleep` — sin `sleep()`/`usleep()`.
- `Perafan/test_conventions_no_only` — sin `->only()` mergeado (autofix: strip).
- `Perafan/test_conventions_no_absolute_paths` — sin `/Users/` ni `/home/` en strings literales (configurable).
- `Perafan/test_conventions_partial_mock_comment` — config `policy` (`forbid`/`require`/`allow`). Resuelve el conflicto §5.3 entre proyectos.

### Changed

- `templates/pint.json` removido — Pint v1.27 no descubre custom fixers desde `pint.json`. Reemplazado por `templates/.php-cs-fixer.dist.php` con los 11 fixers activos.
- Documentación del README actualizada al flujo real con `.php-cs-fixer.dist.php`.

### Total

- 11 fixers (10 mecanizados + 1 con config policy).
- R10 y R12 quedan code-review-only en v1.0 — sus patrones (scope semántico de `try/catch`, chain de method calls) son inviables limpiamente sobre Tokens.
- Suite Pest: 55 tests pasando, 65 assertions.

## [0.2.0] - 2026-05-19

### Added

- 4 Pint custom fixers piloto:
  - `Perafan/test_conventions_max_description_length` — descripcion ≤ 50 chars (configurable).
  - `Perafan/test_conventions_no_should_prefix` — sin `should `/`it tests `/`tests that ` (autofix).
  - `Perafan/test_conventions_forbidden_matchers` — `toBe(true|false|null)` → semantic matchers (autofix).
  - `Perafan/test_conventions_no_app_mocking` — no mockear `App\…` (configurable).
- Utilidades de Tokens: `PestCallFinder`, `PestCall`, `NamespaceResolver`.
- `AbstractTestConventionsFixer` con helper `lineFor()` para errores diagnosticos.
- Suite Pest con 25 tests, dogfooding via `.php-cs-fixer.dist.php` propio.

### Direction

- **Distribucion como Pint custom fixers** (no como binario CLI propio). Razon: Pint ya corre en cada repo cliente, reusarlo elimina infra extra. `--fix` autofix nativo. Editor inline gratis.
- Reglas R10 y R12 quedan code-review-only en v1.0 — sus patrones son inviables limpiamente sobre Tokens. Si aparece dolor real, sprint para implementarlas.

### Roadmap

- Fase 1 — Estabilizar `CONVENTIONS.md` v0.1.0.
- Fase 2 — Implementar 4 Pint fixers piloto (R02, R03, R04, R06). Tag `v0.2.0`.
- Fase 3 — Primer cliente real adoptado. Tag `v0.3.0`.
- Fase 4 — Segundo cliente + 10 fixers totales + el de config §5.3. Tag `v1.0.0`.
- Fase 5 — Plugin Claude Code distribuible.

[Unreleased]: https://github.com/Perafan18/test-conventions/commits/main
