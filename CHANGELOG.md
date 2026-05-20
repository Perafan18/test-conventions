# Changelog

Todos los cambios notables a este proyecto se documentan aca.

Formato basado en [Keep a Changelog](https://keepachangelog.com/en/1.1.0/) y [SemVer](https://semver.org/spec/v2.0.0.html).

## [Unreleased]

### Added

- Esqueleto inicial del repo: estructura de directorios (`src/`, `templates/`, `tests/`), composer.json, README, LICENSE.
- Working-draft v0.1 de `CONVENTIONS.md` extraido del intersect comun entre docs piloto de convenciones Pest 4 + Laravel.
- Esqueleto `.claude-plugin/` para el skill `test-conventions`.
- GitHub Actions workflow CI placeholder.
- `templates/pint.json` template con las rules activas que el cliente puede copiar.

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
