<?php

declare(strict_types=1);

use Perafan\Pinto\Fixers\NoOnlyFixer;

beforeEach(function () {
    $this->fixer = new NoOnlyFixer();
});

it('strips only() from chained call', function () {
    $code = "<?php\n\nit('does X', function () {})->only();\n";

    expect(applyFixer($this->fixer, $code))
        ->toContain("function () {})")
        ->not->toContain('->only()');
});

it('strips only() from middle of chain', function () {
    $code = "<?php\n\nit('does X', function () {})->only()->skip('reason');\n";

    expect(applyFixer($this->fixer, $code))
        ->toContain("->skip('reason')")
        ->not->toContain('->only()');
});

it('leaves unrelated method calls alone', function () {
    $code = "<?php\n\nit('does X', function () {})->with(\$dataset);\n";

    expect(applyFixer($this->fixer, $code))
        ->toContain('->with(');
});
