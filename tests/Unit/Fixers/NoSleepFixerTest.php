<?php

declare(strict_types=1);

use Perafan\TestConventions\Fixers\NoSleepFixer;

beforeEach(function () {
    $this->fixer = new NoSleepFixer();
});

it('fails on sleep call', function () {
    $code = "<?php\n\nsleep(2);\n";

    expect(tryApplyFixer($this->fixer, $code))
        ->toContain('`sleep()` is forbidden');
});

it('fails on usleep call', function () {
    $code = "<?php\n\nusleep(500);\n";

    expect(tryApplyFixer($this->fixer, $code))
        ->toContain('`usleep()` is forbidden');
});

it('ignores method sleep calls', function () {
    $code = "<?php\n\n\$obj->sleep(2);\n";

    expect(tryApplyFixer($this->fixer, $code))->toBeNull();
});

it('ignores static sleep calls', function () {
    $code = "<?php\n\nFoo::sleep(2);\n";

    expect(tryApplyFixer($this->fixer, $code))->toBeNull();
});

it('ignores function declarations named sleep', function () {
    $code = "<?php\n\nfunction sleep(\$n) {}\n";

    expect(tryApplyFixer($this->fixer, $code))->toBeNull();
});
