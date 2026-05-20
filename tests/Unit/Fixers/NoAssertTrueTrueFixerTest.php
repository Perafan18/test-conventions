<?php

declare(strict_types=1);

use Perafan\TestConventions\Fixers\NoAssertTrueTrueFixer;

beforeEach(function () {
    $this->fixer = new NoAssertTrueTrueFixer();
});

it('fails on assertTrue(true)', function () {
    $code = "<?php\n\nassertTrue(true);\n";

    expect(tryApplyFixer($this->fixer, $code))
        ->toContain('Placeholder assertion `assertTrue(true)`');
});

it('fails on expect(true)->toBeTrue()', function () {
    $code = "<?php\n\nexpect(true)->toBeTrue();\n";

    expect(tryApplyFixer($this->fixer, $code))
        ->toContain('Placeholder assertion `expect(true)->toBeTrue()`');
});

it('passes on real assertions', function () {
    $code = "<?php\n\nassertTrue(\$x);\nexpect(\$y)->toBeTrue();\n";

    expect(tryApplyFixer($this->fixer, $code))->toBeNull();
});

it('ignores method assertTrue calls', function () {
    $code = "<?php\n\n\$obj->assertTrue(true);\n";

    expect(tryApplyFixer($this->fixer, $code))
        ->toBeNull();
});
