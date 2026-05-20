# Changelog

Todos los cambios notables a este proyecto se documentan aca.

Formato basado en [Keep a Changelog](https://keepachangelog.com/en/1.1.0/) y [SemVer](https://semver.org/spec/v2.0.0.html).

## [Unreleased]

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
