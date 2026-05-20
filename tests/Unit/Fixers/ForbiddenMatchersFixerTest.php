<?php

declare(strict_types=1);

use Perafan\TestConventions\Fixers\ForbiddenMatchersFixer;

beforeEach(function () {
    $this->fixer = new ForbiddenMatchersFixer();
});

it('rewrites toBe(true) to toBeTrue()', function () {
    $code = "<?php\n\nexpect(\$x)->toBe(true);\n";

    expect(applyFixer($this->fixer, $code))
        ->toContain('toBeTrue()')
        ->not->toContain('toBe(true)');
});

it('rewrites toBe(false) to toBeFalse()', function () {
    $code = "<?php\n\nexpect(\$x)->toBe(false);\n";

    expect(applyFixer($this->fixer, $code))
        ->toContain('toBeFalse()')
        ->not->toContain('toBe(false)');
});

it('rewrites toBe(null) to toBeNull()', function () {
    $code = "<?php\n\nexpect(\$x)->toBe(null);\n";

    expect(applyFixer($this->fixer, $code))
        ->toContain('toBeNull()')
        ->not->toContain('toBe(null)');
});

it('preserves toBe with non-boolean arguments', function () {
    $code = "<?php\n\nexpect(\$x)->toBe('hello');\n";

    expect(applyFixer($this->fixer, $code))
        ->toContain("toBe('hello')");
});

it('does not affect unrelated method calls', function () {
    $code = "<?php\n\n\$service->doSomething(true);\n";

    expect(applyFixer($this->fixer, $code))
        ->toContain('doSomething(true)');
});

it('chains correctly after rewrite', function () {
    $code = "<?php\n\nexpect(\$x)->toBe(true)->and(\$y);\n";
    $result = applyFixer($this->fixer, $code);

    expect($result)->toContain('toBeTrue()->and');
});
