<?php

declare(strict_types=1);

use Perafan\TestConventions\Fixers\NoPauseInBrowserFixer;

beforeEach(function () {
    $this->fixer = new NoPauseInBrowserFixer();
    $this->fixer->configure([]);
});

function applyBrowserFixer($fixer, string $code, string $path): ?string
{
    $tokens = PhpCsFixer\Tokenizer\Tokens::fromCode($code);

    try {
        $fixer->fix(new SplFileInfo($path), $tokens);
    } catch (RuntimeException $exception) {
        return $exception->getMessage();
    }

    return null;
}

it('fails on pause in browser path', function () {
    $code = "<?php\n\n\$browser->pause(2000);\n";

    expect(applyBrowserFixer($this->fixer, $code, 'tests/Browser/Login.php'))
        ->toContain('forbidden');
});

it('fails on wait in browser path', function () {
    $code = "<?php\n\n\$browser->wait(500);\n";

    expect(applyBrowserFixer($this->fixer, $code, 'tests/Browser/Login.php'))
        ->toContain('forbidden');
});

it('passes on pause in feature path', function () {
    $code = "<?php\n\n\$browser->pause(2000);\n";

    expect(applyBrowserFixer($this->fixer, $code, 'tests/Feature/Login.php'))
        ->toBeNull();
});

it('respects custom paths configuration', function () {
    $this->fixer->configure(['paths' => ['/MyDir/']]);
    $code = "<?php\n\n\$browser->pause(2000);\n";

    expect(applyBrowserFixer($this->fixer, $code, 'tests/MyDir/Login.php'))
        ->toContain('forbidden');
});
