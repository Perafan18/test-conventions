# Convenciones de tests Pest 4 + Laravel

> **Working draft v0.1.0.** Esta es la versión inicial del doc canónico. Versión estable saldrá tras la Fase 1 del [roadmap del repo](README.md#roadmap). Hasta entonces, la estructura es estable pero el detalle de algunas secciones puede afinar.

**Audiencia:** cualquiera que escriba, edite o revise un test en un proyecto que adopta este estándar — humano o agente.

**Regla de oro:** antes de escribir un test, lee este doc. Es el yardstick. Inspirado en [betterspecs.org](https://betterspecs.org) (guía RSpec de la comunidad Ruby), adaptado a Pest 4 + Laravel 13+.

Cualquier discrepancia entre lo que ves en `tests/` y lo que dice este doc se resuelve **a favor del doc**. Si encuentras un test que no lo cumple, está pendiente de refactor o es deuda registrada — no es licencia para escribir otro igual.

---

## 1. Filosofía

Un buen test prueba **comportamiento observable**, no implementación. Falla por la razón correcta, pasa rápido, y se lee como una oración en pasado simple. Si necesitas explicar qué hace, está mal escrito.

Tres preguntas que un test debe responder en su descripción + cuerpo:

1. **¿Qué situación?** (Arrange)
2. **¿Qué acción?** (Act)
3. **¿Qué resultado observable?** (Assert)

Si un agente lee solo el `it('...')` y no entiende qué se está probando, fallaste antes de la primera línea.

### Para quién testeamos

| Test | Pregunta que responde | Cuándo se rompe |
|---|---|---|
| Unit | ¿Esta función hace lo que dice su firma? | El contrato cambió |
| Feature | ¿Este flujo HTTP/job/comando produce el outcome esperado? | El sistema dejó de cumplir su promesa |
| Browser (Pest 4 / Dusk) | ¿Un humano puede usar la pantalla sin que JS reviente? | La integración cliente-servidor se rompió |

Nunca confundas las capas. Un test que mockea Eloquent en Unit para "no tocar DB" mide implementación, no comportamiento.

---

## 2. Estructura del archivo

### 2.1 `it()` siempre, `test()` casi nunca

```php
// ✅
it('belongs to a user', function () {
    // ...
});

// ❌
test('post belongs to user', function () { /* ... */ });
```

`test()` queda permitido **solo** para arch tests (`tests/Unit/ArchTest.php`) y assertions sobre configs estáticas donde "it" suena raro. En el resto, `it()` es obligatorio.

### 2.2 Descripciones en inglés, ≤ 50 caracteres

**Idioma: inglés, siempre.** El resto del repo (UI copy, dominio de producto, documentación) puede estar en otro idioma, pero los tests son herramienta técnica y se mantienen en el idioma del ecosistema (Laravel/Pest OSS, framework docs, packages).

- ✅ `it('belongs to a user')`
- ✅ `it('rejects duplicate emails on register')`
- ✅ `it('redirects to /home after login')`
- ❌ `it('should belong to a user')` — sin "should"
- ❌ `it('it tests that the model has a user')` — sin "it tests"
- ❌ `it('tests scopeActive filters where active is true')` — no nombres el método; nombras el comportamiento

**Tercera persona del verbo, presente indicativo.** No "should filter", sino "filters". No "we test that", sino la acción directa.

**Por qué 50 chars:** betterspecs.org dice 40; lo subimos a 50 para dar aire a frases naturales sin permitir one-liners-novela.

**Por qué inglés aunque el proyecto sea en otro idioma:**

1. **Estándar de facto en Laravel/Pest.** Filament, Livewire, framework, Spatie packages — todos usan inglés. Reduce fricción cuando saltas entre repos.
2. **Más conciso.** "belongs to a user" (18) vs equivalentes en otros idiomas suelen pasarse de chars.
3. **Vocabulario técnico naturalmente en inglés.** `scope`, `factory`, `polymorphic`, `BelongsTo`, `MorphToMany`, `casts`, `datasets` — code-switch queda forzado.
4. **Onboarding.** Si entra alguien externo al proyecto, los tests le hablan.

### 2.3 `describe()` solo cuando hay > 4 tests

Si el archivo tiene 1-4 tests, plano:

```php
it('does X', function () { /* ... */ });
it('does Y', function () { /* ... */ });
```

Si tiene > 4 y hay subgrupos claros, `describe()` con nombres de contexto:

```php
describe('when the user is an admin', function () {
    it('can promote others', function () { /* ... */ });
    it('can transfer ownership', function () { /* ... */ });
});

describe('when the user is a viewer', function () {
    it('cannot mutate roles', function () { /* ... */ });
});
```

Anida `describe()` solo si hay sub-contextos genuinos. Tres niveles de anidación es señal de que el archivo merece partirse.

### 2.4 AAA visible — Arrange / Act / Assert

Bloques separados por líneas en blanco. El Act debe ser identificable de un vistazo (típicamente 1 línea).

```php
it('publishes the post', function () {
    $post = Post::factory()->draft()->create();           // Arrange

    $post->publish();                                      // Act

    expect($post->fresh())->toBePublished();               // Assert
});
```

Si tu Act tiene 5 líneas, probablemente estás probando dos cosas. Pártelo.

### 2.5 Orden del archivo

```php
<?php

use App\Models\Post;
use App\Models\User;
// ... imports

// 1. uses() — solo si necesitas algo distinto al default de tests/Pest.php

// 2. helpers locales privados (si los hay)
function buildPostWithComments(int $count): Post { /* ... */ }

// 3. tests
it('does X', function () { /* ... */ });
```

Si un helper se usa en ≥ 3 archivos, promovelo a `tests/Pest.php`. Si solo en este, queda local.

---

## 3. Datos

### 3.1 Factories sobre seeders sobre raw DB

| Caso | Herramienta |
|---|---|
| Crear un modelo para un test | `Model::factory()->create()` |
| Estado conocido para Browser | Factory + helpers en `tests/Pest.php` |
| Insertar fila bypaseando observers/middleware **a propósito** | `DB::table()->insert()` con helper documentado |

Si te encuentras haciendo `DB::insert` para algo que tiene factory, párate. Casi siempre estás evadiendo un side-effect que el test debería honrar — y si lo evades, no estás probando el sistema real.

### 3.2 Solo seteas lo que el test reclama

```php
// ✅ El test trata sobre el rol. Solo overrideo lo relevante.
it('attaches an admin role', function () {
    $user = User::factory()->admin()->create();

    expect($user)->toHaveRole('admin');
});

// ❌ ¿Por qué seteas name, locale, plan si no estás probando ninguno?
it('attaches an admin role', function () {
    $user = User::factory()->create([
        'name' => 'Alice',
        'locale' => 'es',
        'plan' => 'free',
        'email_verified_at' => now(),
    ]);
    // ...
});
```

Si necesitas muchos overrides, **arregla la factory, no el test**. Una factory con defaults pobres infecta cada test que la usa.

### 3.3 Estados de factory para variantes recurrentes

```php
User::factory()->unverified()->create();
User::factory()->admin()->create();
Post::factory()->published()->create();
Post::factory()->draft()->create();
```

Si tres tests escriben `User::factory()->create(['email_verified_at' => null])`, eso ya es `unverified()`. Muévelo.

### 3.4 Tiempo: `Carbon::setTestNow()`, nunca strings hardcodeados

```php
// ✅
Carbon::setTestNow('2026-01-15 10:00:00');
$post = Post::factory()->create();
expect($post->created_at->toDateString())->toBe('2026-01-15');

// ❌ ¿Qué pasa con este test en 2027?
$post = Post::factory()->create(['created_at' => '2024-01-01']);
```

Excepción: cuando el test es justamente sobre una fecha fija. Ahí la fecha es parte del dominio, no scaffolding.

### 3.5 Aleatoriedad: congélala

UUIDs, slugs random, fakers — si tu assertion depende del valor, congélalo:

```php
Str::createUuidsUsing(fn () => 'fixed-uuid-for-test');
```

O mejor, no afirmes valores aleatorios. Afirma relaciones (`$post->user_id === $user->id`).

---

## 4. Expectativas

### 4.1 Un concepto por `it()`

Un concepto, no una sola `expect()`. Está bien encadenar varias si todas verifican el mismo outcome:

```php
// ✅ Mismo concepto: "el POST publica el artículo"
it('publishes the post on POST', function () {
    $post = Post::factory()->draft()->create();

    $response = $this->post("/admin/posts/{$post->id}/publish");

    $response->assertRedirect();
    expect($post->fresh())->toBePublished();
});

// ❌ Dos conceptos en un solo it()
it('publishes the post and notifies the author', function () {
    // pártelo en dos tests
});
```

### 4.2 Matchers semánticos sobre genéricos

| Preferido | Evitar |
|---|---|
| `toBeTrue()` | `toBe(true)` |
| `toBeFalse()` | `toBe(false)` |
| `toBeNull()` | `toBe(null)` |
| `toBeEmpty()` | `toHaveCount(0)` |
| `toBeInstanceOf(Post::class)` | `get_class($x)->toBe(Post::class)` |
| `toContain('hola')` | `str_contains(...)->toBeTrue()` |
| `toHaveCount(3)` | `count($x)->toBe(3)` |
| `toBeGreaterThan(0)` | `($x > 0)->toBeTrue()` |

### 4.3 Custom expectations cuando hay regla del tres

**Regla del tres:** si tres tests escriben la misma cadena de afirmaciones sobre la misma SUT, extrae custom expectation. Esas viven en `tests/Pest.php` del proyecto cliente. El paquete **no** las empaqueta — son específicas del dominio del proyecto (ver [ADR-003](README.md#adr-003-sin-custom-expectations-en-el-paquete)).

Patrón: primero `toBeInstanceOf`, después el assert de dominio. Una expectation = un concepto.

```php
expect()->extend('toBePublished', function () {
    return $this
        ->toBeInstanceOf(Post::class)
        ->and($this->value->published_at)->not->toBeNull();
});
```

### 4.4 Afirma estados, no ausencias

```php
// ✅
expect($post)->toBeDraft();

// ⚠️ funciona, pero menos expresivo
expect($post->published_at)->toBeNull();
```

`toBeNull()` está bien cuando el null *es* el dominio. Cuando el null es la consecuencia de un estado (borrador), afirma el estado.

---

## 5. Mocking

### 5.1 Mockea solo en los bordes del sistema

**SÍ mockeas** (siempre con fakes de Laravel):

| Externo | Fake |
|---|---|
| HTTP a APIs externas | `Http::fake(['api.example.com/*' => Http::response(...)])` |
| Storage S3/local | `Storage::fake('s3')`, `Storage::fake('local')` |
| Queue (en Feature tests) | `Queue::fake()` / `Bus::fake()` |
| Mail | `Mail::fake()` |
| Notifications | `Notification::fake()` |
| Eventos (solo verificar dispatch) | `Event::fake([SomeEvent::class])` |
| Broadcasts (en CI) | `BROADCAST_CONNECTION=null` en `phpunit.xml` |

**NUNCA mockeas:**

- Modelos Eloquent — usa factories
- Repositorios/servicios propios — usa la implementación real
- Policies — `RefreshDatabase` te cubre con DB en memoria
- Config — usa `config()->set('key', $value)` dentro del test

> Mockear código propio mide implementación, no comportamiento. Si refactorizas el servicio sin cambiar su outcome, tus tests no deberían romperse — y si mockeas sus internals, lo harán.

### 5.2 `Http::fake()` con respuestas por URL

```php
// ✅
Http::fake([
    'api.example.com/v1/items' => Http::response(['id' => 1], 200),
    'api.other.com/*' => Http::response([], 204),
]);

Http::assertSent(fn ($request) => str_starts_with($request->url(), 'https://api.example.com'));

// ❌
$this->mock(ExternalClient::class)->shouldReceive('fetch')->andReturn(...);
```

### 5.3 Partial mocks: opción configurable

`$this->partialMock(Service::class)` solo cuando el SUT tiene un método externo costoso (PDF rendering, ffmpeg, queue release()) que no se puede observar de otra forma.

**Comments policy.** Este es un punto donde proyectos legítimos discrepan. La regla `partial_mock_comment_policy` en `test-conventions.php` toma la decisión por proyecto:

| Valor | Comportamiento |
|---|---|
| `forbid` | Sin comentario inline. Si el patrón es oscuro, extraé fake explícito. La comments policy del proyecto gana |
| `require` | Comentario inline obligatorio explicando *por qué*. Es la única excepción a la regla "cero comentarios" en tests |
| `allow` | Sin opinión |

Detalle de la decisión: [Comments policy (conflicto §5.3)](README.md#comments-policy-conflicto-53).

---

## 6. Datasets sobre loops

```php
// ✅
it('accepts valid statuses', function (string $status) {
    expect(Post::isValidStatus($status))->toBeTrue();
})->with(['draft', 'published', 'archived']);

// ❌
it('accepts valid statuses', function () {
    foreach (['draft', 'published', 'archived'] as $status) {
        expect(Post::isValidStatus($status))->toBeTrue();
    }
});
```

**Por qué:** Pest reporta cada caso del dataset por separado. Si `archived` falla, ves cuál falló. Con loop, un fallo enmascara el resto.

**Cuándo:** > 3 casos iguales con datos distintos. Para 2-3 casos, dos `it()` por separado es más legible.

**Datasets nombrados** para casos descriptivos:

```php
it('rejects invalid emails', function (string $email) {
    expect(User::factory()->make(['email' => $email])->isValid())->toBeFalse();
})->with([
    'missing @' => 'foobar.com',
    'missing TLD' => 'foo@bar',
    'double @' => 'foo@@bar.com',
]);
```

---

## 7. Browser tests

### 7.1 Engine: Pest 4 Browser o Dusk

El doc cubre ambos. Cada proyecto elige uno según su stack:

- **Pest 4 Browser** — idiomático para Pest 4+, sintaxis `visit(...)->click(...)->assertSee(...)`.
- **Dusk** — legacy pero estable, Page Object chainable.

### 7.2 Page objects o equivalente

Si una página aparece en > 1 test, tiene Page Object (Dusk) o Page class minimal (Pest 4 Browser con `ROUTE` const + selectores reusables). Selectores como constantes/métodos, nunca strings sueltos en el test.

### 7.3 Idempotencia

Cada test crea su propia data vía factories o helpers. Nunca asume orden ni estado previo.

### 7.4 Esperas semánticas

```php
// ✅
$browser->waitForText('Welcome', 5);
$browser->waitFor('@some-button');

// ❌ flaky garantizado
$browser->pause(2000)->assertSee('Welcome');
```

En Pest 4 Browser, `assertSee*` auto-esperan al timeout configurado (5s default).

### 7.5 Browser solo para JS real

Browser no es para validar formularios — eso se prueba con Livewire testing API:

```php
// ✅ Feature test (Livewire)
livewire(LoginForm::class)
    ->set('email', 'foo@bar')
    ->call('submit')
    ->assertHasErrors(['email' => 'email']);

// ❌ overkill en Browser
$browser->visit('/login')->type('email', 'foo@bar')->press('Submit')->assertSee('invalid email');
```

Reserva Browser para: reactividad cross-component, drag-drop, animaciones, dark mode toggle, real-time updates. Lo que **solo** se puede ver en navegador.

---

## 8. Anti-patrones — lista cerrada

Rechazamos en code review. No hay grises.

1. **`if`/`switch` dentro del test.** Si ramifica, son N tests. Usa datasets o tests separados.
2. **`try/catch` para verificar excepciones.** Usa `expect(fn () => $sut->boom())->toThrow(MyException::class)`.
3. **`assertTrue(true)` o tests sin `expect()`.** Risky test = fail en review.
4. **Aserciones sin contenido.** `expect($result)->toBeArray()` solo no sirve — afirma la forma del array o su contenido.
5. **`sleep()` / `pause()` con timeout fijo.** Sin excepción. `waitFor*` en Browser; `Carbon::setTestNow` + `travelTo` en Feature.
6. **Tests que dependen del orden de ejecución.** `RefreshDatabase` garantiza fresh state — si tu test asume otro, está roto.
7. **`refresh()` después de cada operación "por las dudas".** Solo refrescas cuando el modelo se modificó *afuera* de tu referencia en memoria (job, evento, observer).
8. **Aserciones contra IDs auto-incrementales (`->toBe(1)`).** SQLite reinicia y Postgres no — flaky cross-driver. Afirma relaciones (`->id`) contra el objeto creado.
9. **Mockear código propio.** Si `MyService` llama a `MyOtherService` y los dos son nuestros, no mockees el segundo.
10. **Snapshots de HTML/JSON gigantes** sin verificar el campo específico. Si te importa el `title`, afirma `title`.
11. **Comentarios narrando el test** (`// Now we check that...`). Si necesitas narrar, refactoriza la descripción del `it()`.
12. **`->only`/`->skip` mergeados a main.** `->skip` requiere razón documentada y issue tracker. `->only` nunca llega a PR.
13. **Hardcodear paths absolutos** (`/Users/foo/...`). Usa `base_path()`, `storage_path()`, `Storage::fake()`.

---

## 9. Mecánica

### 9.1 Speed targets

| Capa | Tiempo máximo por test |
|---|---|
| Unit | 50 ms |
| Feature | 300 ms |
| Browser | 5 s |

Tests más lentos llevan `->skip('lento: ver #issue')` con issue de optimización abierto. No se mergean "por ahora".

### 9.2 Custom expectations y helpers en `tests/Pest.php`

Custom expectations: catalogadas en §4.3. Patrón: `toBeInstanceOf` primero, después assert de dominio. Una expectation = un concepto.

Helpers globales: cuando un helper local se usa en ≥ 3 archivos, promovelo. Nombres descriptivos del dominio del proyecto.

---

## 10. Apéndice: betterspecs.org → Pest 4

Mapping de rules de betterspecs.org (RSpec) a su equivalente en este estándar.

| betterspecs.org | Equivalente Pest 4 | Sección |
|---|---|---|
| `describe '#method'` / `'.method'` | `describe()` solo si > 4 tests sobre la misma SUT | §2.3 |
| `context 'when X'` | `describe('when X')` anidado, o archivos separados | §2.3 |
| Description ≤ 40 chars | Description ≤ 50 chars en inglés | §2.2 |
| Single expectation test | Un *concepto* por `it()`; múltiples `expect()` permitidas si miden el mismo outcome | §4.1 |
| Test all possible cases | Datasets (`->with(...)`) — un fallo aislado por caso | §6 |
| `expect` syntax | Solo `expect()`; nunca `assert*` en código nuevo | §4 |
| `subject { ... }` | `beforeEach(fn () => $this->subject = ...)`, o construcción inline | §3 |
| `let` / `let!` (lazy memo) | **No existe en Pest.** Usa `beforeEach` (eager), helpers, o datasets | §3 |
| Mock at boundaries | Laravel fakes (`Http::fake`, `Storage::fake`, etc.) | §5.1 |
| Create only data you need | Factory + overrides mínimos | §3.2 |
| Factories not fixtures | Laravel Factories siempre | §3.1 |
| Easy to read matchers | Matchers semánticos + custom expectations | §4.2, §4.3 |
| Shared examples | Datasets + custom expectations; sin `it_behaves_like` | §6, §4.3 |
| Test behavior, not implementation | No mockear código propio; assert sobre outcomes | §5.1 |
| Stub external services | `Http::fake` por URL; nunca mockear `HttpClient` | §5.2 |
| Run tests in random order | Pest 4 randomiza por default — no rebajar | §9 |
| Speed up tests | Targets en §9.1 | §9.1 |
| Use the right matcher | Tabla §4.2 | §4.2 |
| Easy to read | Descripciones en inglés narrando comportamiento; AAA visible | §2.2, §2.4 |

### Diferencias filosóficas con betterspecs

1. **Inglés siempre** en descripciones — el resto del proyecto puede estar en otro idioma, pero los tests son herramienta técnica y siguen el idioma del ecosistema Laravel/Pest. Code-switch con términos técnicos se lee peor que inglés uniforme.
2. **50 chars sobre 40** — los 40 de betterspecs son razonables para inglés, pero permitimos 10 más para frases naturales sin habilitar one-liners-novela.
3. **No `let` lazy** — Pest no lo soporta y la community PHP no lo extraña. `beforeEach` eager es suficiente.

---

## 11. Cómo aplicar este doc

**Antes de escribir un test:**

1. Lee este doc (o al menos §2-§4 y §8).
2. Si el test mockea código del proyecto o duplica un patrón ya existente, párate y revisa.
3. Si tienes dudas sobre un patrón, busca un ejemplo en `tests/` que ya lo siga — el código es la documentación canónica.

**Cuando revises un PR con tests:**

Marca violaciones contra la lista de anti-patrones (§8). Son razones legítimas para pedir cambios.

**Cuando este doc no cubra tu caso:**

Hay tres opciones:

- Es genuinamente nuevo: agrégalo acá, en un PR aparte de la feature.
- Es una excepción justificada: documéntala inline (el único caso donde un comentario en test es válido, sujeto a `partial_mock_comment_policy`).
- No es excepción ni laguna, no leíste bien: ver opción 1.
