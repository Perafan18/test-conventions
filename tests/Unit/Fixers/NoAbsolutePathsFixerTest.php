<?php

declare(strict_types=1);

use Perafan\Pinto\Fixers\NoAbsolutePathsFixer;

beforeEach(function () {
    $this->fixer = new NoAbsolutePathsFixer();
    $this->fixer->configure([]);
});

it('fails on absolute Users path', function () {
    $code = "<?php\n\n\$path = '/Users/me/file.txt';\n";

    expect(tryApplyFixer($this->fixer, $code))
        ->toContain('Absolute path forbidden');
});

it('fails on absolute home path', function () {
    $code = "<?php\n\n\$path = '/home/me/file.txt';\n";

    expect(tryApplyFixer($this->fixer, $code))
        ->toContain('Absolute path forbidden');
});

it('passes on relative paths', function () {
    $code = "<?php\n\n\$path = 'tests/Fixtures/file.txt';\n";

    expect(tryApplyFixer($this->fixer, $code))->toBeNull();
});

it('passes on base_path() helper', function () {
    $code = "<?php\n\n\$path = base_path('tests/Fixtures');\n";

    expect(tryApplyFixer($this->fixer, $code))->toBeNull();
});

it('respects custom prefixes', function () {
    $this->fixer->configure(['prefixes' => ['/var/']]);
    $code = "<?php\n\n\$path = '/var/log/app.log';\n";

    expect(tryApplyFixer($this->fixer, $code))
        ->toContain('Absolute path forbidden');
});
