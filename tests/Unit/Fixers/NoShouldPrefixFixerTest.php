<?php

declare(strict_types=1);

use Perafan\Pinto\Fixers\NoShouldPrefixFixer;

beforeEach(function () {
    $this->fixer = new NoShouldPrefixFixer();
    $this->fixer->configure([]);
});

it('passes when description has no forbidden prefix', function () {
    $code = "<?php\n\nit('does something', function () {});\n";

    expect(applyFixer($this->fixer, $code))->toContain("'does something'");
});

it('strips should prefix automatically', function () {
    $code = "<?php\n\nit('should do something', function () {});\n";
    $result = applyFixer($this->fixer, $code);

    expect($result)->toContain("'do something'");
    expect($result)->not->toContain("'should");
});

it('strips it tests prefix automatically', function () {
    $code = "<?php\n\nit('it tests something', function () {});\n";
    $result = applyFixer($this->fixer, $code);

    expect($result)->toContain("'something'");
});

it('strips tests that prefix automatically', function () {
    $code = "<?php\n\nit('tests that something works', function () {});\n";
    $result = applyFixer($this->fixer, $code);

    expect($result)->toContain("'something works'");
});

it('leaves partial prefix unchanged', function () {
    $code = "<?php\n\nit('shouldnt match because no space', function () {});\n";

    expect(applyFixer($this->fixer, $code))->toContain("'shouldnt");
});

it('accepts custom prefix list', function () {
    $this->fixer->configure(['prefixes' => ['MUST ']]);
    $code = "<?php\n\nit('MUST do this', function () {});\n";

    expect(applyFixer($this->fixer, $code))->toContain("'do this'");
});
