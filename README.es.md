# pinto

> Convenciones de tests Pest 4 + Laravel como **custom fixers de Pint** + doc canonico versionado + plugin Claude Code.

[![License: MIT](https://img.shields.io/badge/License-MIT-yellow.svg)](#licencia)
[![PHP](https://img.shields.io/badge/PHP-8.2%2B-blue.svg)]()
[![Status](https://img.shields.io/badge/status-pre--release-orange.svg)]()

## Que es

Un sistema de **tres componentes** que estandariza convenciones de tests Pest 4 + Laravel:

1. **Doc canonico** — [`CONVENTIONS.md`](CONVENTIONS.md) versionado con git tags. El yardstick humano-legible.
2. **Paquete Composer de Pint custom fixers** — un fixer por regla mecanizable. Se carga via `pint.json` del cliente; corre con el pipeline Pint que el cliente ya tiene.
3. **Plugin Claude Code** — skill que carga el doc en context cuando un agente escribe tests.

Los tres viven en este repo y se liberan juntos via SemVer.

## Por que existe

Cada proyecto Laravel/Pest tiende a derivar su propio doc de convenciones que ~85% se solapa con los demas. Cuando el doc evoluciona en un proyecto, la mejora no llega a los otros sin trabajo manual — drift sutil en 6 meses.

Algunos proyectos meten las reglas mecanizables como `tests/Unit/ConventionsTest.php`. Patron pragmatico pero categorialmente confuso: es un *lint*, no un test del SUT. Este paquete resuelve la duplicacion con una sola fuente de verdad, y resuelve la confusion **distribuyendolas como custom fixers de Pint**: cero infra extra del cliente, autofix nativo, editor inline gratis.

## Estado actual

**Pre-release.** El doc canonico esta en working-draft. Los fixers estan por implementarse. La API de configuracion puede cambiar antes de `v1.0.0`.

No usar todavia en produccion.

## Instalacion

```bash
composer require --dev perafan/pinto
vendor/bin/pinto init
```

`init` genera dos archivos:

- **`.php-cs-fixer.dist.php`** — una linea que delega al config del vendor. Permite que plugins PHP-CS-Fixer de editores (PhpStorm, VSCode) den feedback inline gratis.
- **`pinto.php`** — overrides opcionales (paths, allowlist, `partial_mock_comment_policy`, etc.).

Listo. No hay que copiar boilerplate ni mantener una config larga del paquete — vive dentro del vendor.

## Uso

### Comandos

| Comando | Hace |
|---|---|
| `vendor/bin/pinto check` | Reporta violaciones sin modificar archivos. Exit 1 si encuentra alguna |
| `vendor/bin/pinto fix` | Aplica autofixes (`should ` → strip, `toBe(true)` → `toBeTrue()`, etc.) |
| `vendor/bin/pinto list-rules` | Tabla de las 11 reglas con seccion del doc y modo (autofix/detect) |
| `vendor/bin/pinto init` | Bootstrap (genera los dos archivos del cliente) |

Output ejemplo:

```
$ vendor/bin/pinto check
tests/Feature/UserTest.php:14: Pinto/max_description_length Description exceeds 50 chars (got 67): "..."
tests/Unit/PostTest.php:8: Pinto/forbidden_matchers Use toBeTrue() instead of toBe(true)

1 file has autofixable violations. Run `vendor/bin/pinto fix` to apply.
Found 2 violations across 2 files.
```

Formato `file:line: rule: message` clickable en editores y terminales modernas.

### CI

```yaml
- run: vendor/bin/pint --test
- run: vendor/bin/pinto check
```

Pint sigue manejando el preset Laravel + built-ins. `pinto` se encarga solo de las reglas de tests.

### Pre-commit (lefthook)

```yaml
pre-commit:
  commands:
    pint:
      glob: "*.php"
      run: vendor/bin/pint --test {staged_files}
    pinto:
      glob: "tests/**/*.php"
      run: vendor/bin/pinto check {staged_files}
```

### Fixes automaticos disponibles

| Regla | Autofix |
|---|---|
| `no_should_prefix` | strip `should `, `it tests `, `tests that ` |
| `forbidden_matchers` | `toBe(true)` → `toBeTrue()`, `toBe(false)` → `toBeFalse()`, `toBe(null)` → `toBeNull()` |
| `no_only` | strip `->only()` |
| `it_not_test` | rename `test(...)` → `it(...)` (excepto en arch tests) |

### Editor inline

El `.php-cs-fixer.dist.php` que `init` genera delega al config del vendor, asi que plugins PHP-CS-Fixer de PhpStorm y VSCode muestran los errores inline. Gratis, sin configuracion extra.

### Por que no `pint.json`

Pint v1.27 no descubre custom fixers de terceros desde `pint.json`. Probado empiricamente — falla con "unknown fixers". El binario `vendor/bin/pinto` resuelve esto: internamente invoca PHP-CS-Fixer con la config completa registrada, asi el cliente no ve la mecanica. Si Pint upstream agrega soporte algun dia, `init` puede generar un `pint.json` en su lugar.

## Doc canonico

Lee [`CONVENTIONS.md`](CONVENTIONS.md) — es el yardstick humano-legible. 11 secciones cubren filosofia, estructura, datos, expectativas, mocking, datasets, browser tests, anti-patrones, mecanica, y un apendice betterspecs.

## Reglas distribuidas

| ID | Regla | Autofix | Estado |
|---|---|---|---|
| R01 | `it()` siempre, no `test()` top-level | Si (rename) | v0.3 |
| R02 | Descripcion ≤ 50 chars | No | v0.2 |
| R03 | Sin prefijo `should ` / `it tests ` / `tests that ` | Si (strip) | v0.2 |
| R04 | Sin `toBe(true\|false\|null)` | Si (rewrite) | v0.2 |
| R05 | Sin `assertTrue(true)` ni `expect(true)->toBeTrue()` | No | v0.3 |
| R06 | No mockear `App\…` | No | v0.2 |
| R07 | Sin `->pause()` en `tests/Browser/` | No | v0.3 |
| R08 | Sin `sleep()` / `usleep()` en bodies de test | No | v0.3 |
| R09 | Sin `->only()` mergeado | Si (remove) | v0.3 |
| R10 | Sin `try/catch` en bodies de test | No | **code-review-only en v1.0** |
| R11 | Sin paths absolutos `/Users/`, `/home/` | No | v0.3 |
| R12 | Inserts en tablas claves via helpers | No | **code-review-only en v1.0** |
| §5.3 | `partial_mock_comment_policy` (config: forbid/require/allow) | No | v0.3 |

R10 y R12 quedan a code review humano/agente porque requieren scope tracking sobre Tokens (no es viable hacerlo limpio en PHP-CS-Fixer custom fixers). Si aparecen como dolor real (regla del tres: 3+ instancias detectadas en review), sprint dedicado para implementarlas.

## Plugin Claude Code

```
/plugin marketplace add github:Perafan18/pinto
/plugin install pinto
```

El skill carga `CONVENTIONS.md` en context cuando un agente esta escribiendo tests Pest en un proyecto que tiene `perafan/pinto` instalado. Sugiere correr `vendor/bin/php-cs-fixer fix --dry-run` al terminar.

## Roadmap

| Fase | Objetivo |
|---|---|
| 1 | Estabilizar `CONVENTIONS.md` v0.1 |
| 2 | 4 Pint fixers piloto (R02, R03, R04, R06) — `v0.2.0` |
| 3 | Primer cliente real adoptado — `v0.3.0` |
| 4 | Segundo cliente + 10 fixers totales — `v1.0.0` |
| 5 | Plugin Claude Code distribuible |

## Contribuir

Pre-release. Contribuciones externas se abren al alcanzar `v1.0.0`.

El paquete sigue su propio estandar — la suite de tests del paquete debe pasar `vendor/bin/pint --test` (que incluye nuestros propios fixers) antes de cualquier merge. Dogfooding.

## Licencia

[MIT](LICENSE). Copyright © Pedro Perafan.
