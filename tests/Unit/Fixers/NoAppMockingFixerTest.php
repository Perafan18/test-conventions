<?php

declare(strict_types=1);

use Perafan\Pinto\Fixers\NoAppMockingFixer;

beforeEach(function () {
    $this->fixer = new NoAppMockingFixer();
    $this->fixer->configure([]);
});

it('fails when mocking a class in App namespace', function () {
    $code = "<?php\n\nuse App\\Services\\Foo;\n\n\$this->mock(Foo::class);\n";

    expect(tryApplyFixer($this->fixer, $code))
        ->toContain('Cannot mock')
        ->toContain('App\\Services\\Foo');
});

it('fails on fully qualified App mock', function () {
    $code = "<?php\n\n\$this->mock(\\App\\Services\\Bar::class);\n";

    expect(tryApplyFixer($this->fixer, $code))
        ->toContain('Cannot mock')
        ->toContain('App\\Services\\Bar');
});

it('passes when mocking a class outside App namespace', function () {
    $code = "<?php\n\nuse Vendor\\Lib\\Client;\n\n\$this->mock(Client::class);\n";

    expect(tryApplyFixer($this->fixer, $code))->toBeNull();
});

it('detects partialMock calls', function () {
    $code = "<?php\n\nuse App\\Services\\Foo;\n\n\$this->partialMock(Foo::class);\n";

    expect(tryApplyFixer($this->fixer, $code))
        ->toContain('Cannot mock');
});

it('detects spy calls', function () {
    $code = "<?php\n\nuse App\\Services\\Foo;\n\n\$this->spy(Foo::class);\n";

    expect(tryApplyFixer($this->fixer, $code))
        ->toContain('Cannot mock');
});

it('detects Mockery static mock calls', function () {
    $code = "<?php\n\nuse App\\Services\\Foo;\n\nMockery::mock(Foo::class);\n";

    expect(tryApplyFixer($this->fixer, $code))
        ->toContain('Cannot mock');
});

it('respects custom forbidden namespaces', function () {
    $this->fixer->configure(['namespaces' => ['MyDomain\\']]);
    $code = "<?php\n\nuse MyDomain\\Service;\n\n\$this->mock(Service::class);\n";

    expect(tryApplyFixer($this->fixer, $code))
        ->toContain('Cannot mock')
        ->toContain('MyDomain\\Service');
});
