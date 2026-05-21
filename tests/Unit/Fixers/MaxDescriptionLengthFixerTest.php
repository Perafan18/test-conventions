<?php

declare(strict_types=1);

use Perafan\Pinto\Fixers\MaxDescriptionLengthFixer;

beforeEach(function () {
    $this->fixer = new MaxDescriptionLengthFixer();
    $this->fixer->configure([]);
});

it('passes when description is under 50 chars', function () {
    $code = "<?php\n\nit('does something useful', function () {});\n";

    expect(tryApplyFixer($this->fixer, $code))->toBeNull();
});

it('fails when description exceeds 50 chars', function () {
    $code = "<?php\n\nit('this description is way too long and exceeds the configured limit by a lot', function () {});\n";

    expect(tryApplyFixer($this->fixer, $code))
        ->toContain('Description exceeds 50 chars');
});

it('respects custom limit configuration', function () {
    $this->fixer->configure(['limit' => 10]);
    $code = "<?php\n\nit('this is more than ten', function () {});\n";

    expect(tryApplyFixer($this->fixer, $code))
        ->toContain('Description exceeds 10 chars');
});

it('reports line number in error message', function () {
    $code = "<?php\n\n\n\nit('this description is way too long and exceeds the configured limit by a lot', function () {});\n";

    expect(tryApplyFixer($this->fixer, $code))
        ->toContain(':5:');
});

it('checks describe() calls too', function () {
    $code = "<?php\n\ndescribe('this is a long describe block name that exceeds the limit', function () {});\n";

    expect(tryApplyFixer($this->fixer, $code))
        ->toContain('Description exceeds');
});

it('ignores method calls on objects', function () {
    $code = "<?php\n\n\$x->it('this is a long string used as method argument that exceeds limit', \$y);\n";

    expect(tryApplyFixer($this->fixer, $code))->toBeNull();
});
