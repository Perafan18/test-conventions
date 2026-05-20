<?php

declare(strict_types=1);

namespace Perafan\TestConventions\Fixers;

use PhpCsFixer\Fixer\ConfigurableFixerInterface;
use PhpCsFixer\Fixer\ConfigurableFixerTrait;
use PhpCsFixer\FixerConfiguration\FixerConfigurationResolver;
use PhpCsFixer\FixerConfiguration\FixerConfigurationResolverInterface;
use PhpCsFixer\FixerConfiguration\FixerOptionBuilder;
use PhpCsFixer\FixerDefinition\CodeSample;
use PhpCsFixer\FixerDefinition\FixerDefinition;
use PhpCsFixer\FixerDefinition\FixerDefinitionInterface;
use PhpCsFixer\Tokenizer\Tokens;
use RuntimeException;
use SplFileInfo;

final class NoPauseInBrowserFixer extends AbstractTestConventionsFixer implements ConfigurableFixerInterface
{
    use ConfigurableFixerTrait;

    public function getName(): string
    {
        return 'Perafan/test_conventions_no_pause_browser';
    }

    public function getDefinition(): FixerDefinitionInterface
    {
        return new FixerDefinition(
            'Forbid `->pause(N)` and `->wait(N)` with fixed timeouts in browser tests. Use semantic waits (`waitForText`, `waitFor`, `assert*` auto-waits).',
            [new CodeSample("<?php\n\n\$browser->pause(2000);\n")]
        );
    }

    public function isCandidate(Tokens $tokens): bool
    {
        return $tokens->isTokenKindFound(T_STRING);
    }

    protected function createConfigurationDefinition(): FixerConfigurationResolverInterface
    {
        return new FixerConfigurationResolver([
            (new FixerOptionBuilder('paths', 'Paths (substring match against file pathname) where this rule applies.'))
                ->setAllowedTypes(['array'])
                ->setDefault(['/Browser/'])
                ->getOption(),
        ]);
    }

    protected function applyFix(SplFileInfo $file, Tokens $tokens): void
    {
        $pathname = $file->getPathname();

        $applies = false;
        foreach ($this->configuration['paths'] as $needle) {
            if (str_contains($pathname, $needle)) {
                $applies = true;
                break;
            }
        }

        if (! $applies) {
            return;
        }

        $code = $tokens->generateCode();

        if (preg_match_all('/->(pause|wait)\s*\(\s*\d+/', $code, $matches, PREG_OFFSET_CAPTURE)) {
            foreach ($matches[0] as $match) {
                $line = substr_count(substr($code, 0, $match[1]), "\n") + 1;
                $this->report($file, $line, sprintf(
                    '`%s` with fixed timeout is forbidden — use semantic wait.',
                    trim($match[0]),
                ));
            }
        }
    }
}
