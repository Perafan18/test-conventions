<?php

declare(strict_types=1);

use Perafan\Pinto\Fixers\PartialMockCommentPolicyFixer;

beforeEach(function () {
    $this->fixer = new PartialMockCommentPolicyFixer();
});

it('policy allow lets all partialMock calls pass', function () {
    $this->fixer->configure(['policy' => 'allow']);
    $code = "<?php\n\n\$this->partialMock(Foo::class);\n";

    expect(tryApplyFixer($this->fixer, $code))->toBeNull();
});

it('policy forbid fails with comment above', function () {
    $this->fixer->configure(['policy' => 'forbid']);
    $code = "<?php\n\n// partial mock because reasons\n\$this->partialMock(Foo::class);\n";

    expect(tryApplyFixer($this->fixer, $code))
        ->toContain('Inline comment above partialMock() is forbidden');
});

it('policy forbid passes without comment', function () {
    $this->fixer->configure(['policy' => 'forbid']);
    $code = "<?php\n\n\$this->partialMock(Foo::class);\n";

    expect(tryApplyFixer($this->fixer, $code))->toBeNull();
});

it('policy require fails without comment', function () {
    $this->fixer->configure(['policy' => 'require']);
    $code = "<?php\n\n\$this->partialMock(Foo::class);\n";

    expect(tryApplyFixer($this->fixer, $code))
        ->toContain('Inline comment above partialMock() is required');
});

it('policy require passes with comment above', function () {
    $this->fixer->configure(['policy' => 'require']);
    $code = "<?php\n\n// partial mock because rendering is slow\n\$this->partialMock(Foo::class);\n";

    expect(tryApplyFixer($this->fixer, $code))->toBeNull();
});
