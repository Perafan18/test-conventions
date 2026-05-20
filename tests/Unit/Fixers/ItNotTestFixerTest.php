<?php

declare(strict_types=1);

use Perafan\TestConventions\Fixers\ItNotTestFixer;

beforeEach(function () {
    $this->fixer = new ItNotTestFixer();
    $this->fixer->configure([]);
});

it('renames top-level test() to it()', function () {
    $code = "<?php\n\ntest('does X', function () {});\n";

    expect(applyFixer($this->fixer, $code))
        ->toContain("it('does X'")
        ->not->toContain("test('does X'");
});

it('leaves method calls unchanged', function () {
    $code = "<?php\n\n\$obj->test('arg', \$x);\n";

    expect(applyFixer($this->fixer, $code))
        ->toContain('->test(');
});

it('leaves function definitions unchanged', function () {
    $code = "<?php\n\nfunction test(\$x) { return \$x; }\n";

    expect(applyFixer($this->fixer, $code))
        ->toContain('function test(');
});

it('leaves pest test() helper calls unchanged', function () {
    $code = "<?php\n\ntest()->actingAs(\$user);\n";

    expect(applyFixer($this->fixer, $code))
        ->toContain('test()->actingAs');
});

it('leaves test() with non-string first arg', function () {
    $code = "<?php\n\ntest(\$variable, function () {});\n";

    expect(applyFixer($this->fixer, $code))
        ->toContain('test($variable');
});

it('respects allow_in_files for arch tests', function () {
    $this->fixer->configure(['allow_in_files' => ['ArchTest.php']]);
    $code = "<?php\n\ntest('arch check', function () {});\n";

    $tokens = PhpCsFixer\Tokenizer\Tokens::fromCode($code);
    $this->fixer->fix(new SplFileInfo('tests/Unit/ArchTest.php'), $tokens);

    expect($tokens->generateCode())->toContain("test('arch");
});
