# test-conventions

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

> Disponible cuando se libere `v0.1.0`.

```bash
composer require --dev perafan/test-conventions
```

Crear `.php-cs-fixer.dist.php` en la raiz del proyecto:

```php
<?php

use PhpCsFixer\Config;
use PhpCsFixer\Finder;
use Perafan\TestConventions\Fixers\ForbiddenMatchersFixer;
use Perafan\TestConventions\Fixers\MaxDescriptionLengthFixer;
use Perafan\TestConventions\Fixers\NoAppMockingFixer;
use Perafan\TestConventions\Fixers\NoShouldPrefixFixer;

return (new Config())
    ->setRiskyAllowed(false)
    ->registerCustomFixers([
        new MaxDescriptionLengthFixer(),
        new NoShouldPrefixFixer(),
        new ForbiddenMatchersFixer(),
        new NoAppMockingFixer(),
    ])
    ->setRules([
        'Perafan/test_conventions_max_description_length' => true,
        'Perafan/test_conventions_no_should_prefix' => true,
        'Perafan/test_conventions_forbidden_matchers' => true,
        'Perafan/test_conventions_no_app_mocking' => true,
    ])
    ->setFinder(
        (new Finder())->in([__DIR__.'/tests'])
    );
```

> **Por que `.php-cs-fixer.dist.php` y no `pint.json`?** Pint v1.27 no descubre custom fixers de terceros desde `pint.json`. PHP-CS-Fixer directo si lo hace via `registerCustomFixers()`. El cliente sigue usando Pint para el resto de su config (preset Laravel + built-ins); este config aplica solo a nuestras rules. Detalle en CONVENTIONS.md.

## Uso

### CI

Agregar al workflow GitHub Actions:

```yaml
- run: vendor/bin/php-cs-fixer fix --dry-run -vvv
```

(Sigue corriendo `vendor/bin/pint --test` para el resto de la config.)

### Autofix local

```bash
vendor/bin/php-cs-fixer fix
```

Aplica fixes automaticos: `should ` strip, `toBe(true)` → `toBeTrue()`, etc.

### Pre-commit (lefthook)

```yaml
pre-commit:
  commands:
    pint:
      glob: "*.php"
      run: vendor/bin/pint --test {staged_files}
    test-conventions:
      glob: "tests/**/*.php"
      run: vendor/bin/php-cs-fixer fix --dry-run {staged_files}
```

### Autofix local

`vendor/bin/pint` (sin `--test`) aplica fixes automaticos a las reglas autofixables:

| Regla | Autofix |
|---|---|
| `no_should_prefix` | strip `should `, `it tests `, `tests that ` |
| `forbidden_matchers` | `toBe(true)` → `toBeTrue()`, `toBe(false)` → `toBeFalse()`, `toBe(null)` → `toBeNull()` |
| `no_only` | strip `->only()` |
| `it_not_test` | rename `test(...)` → `it(...)` |

### Editor inline

PhpStorm con plugin PHP-CS-Fixer y VSCode con extension PHP-CS-Fixer leen los mismos fixers y los muestran inline. Gratis.

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
/plugin marketplace add github:Perafan18/test-conventions
/plugin install test-conventions
```

El skill carga `CONVENTIONS.md` en context cuando un agente esta escribiendo tests Pest en un proyecto que tiene `perafan/test-conventions` instalado. Sugiere correr `vendor/bin/pint --test` al terminar.

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
