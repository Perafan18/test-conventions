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
use SplFileInfo;

final class NoSleepFixer extends AbstractTestConventionsFixer implements ConfigurableFixerInterface
{
    use ConfigurableFixerTrait;

    public function getName(): string
    {
        return 'Perafan/test_conventions_no_sleep';
    }

    public function getDefinition(): FixerDefinitionInterface
    {
        return new FixerDefinition(
            'Forbid `sleep()` and `usleep()` calls in tests. Use `Carbon::setTestNow()` / `travelTo()` or semantic waits.',
            [new CodeSample("<?php\n\nsleep(2);\n")]
        );
    }

    public function isCandidate(Tokens $tokens): bool
    {
        return $tokens->isTokenKindFound(T_STRING);
    }

    protected function createConfigurationDefinition(): FixerConfigurationResolverInterface
    {
        return new FixerConfigurationResolver([
            (new FixerOptionBuilder('allow_in_files', 'File basenames where sleep()/usleep() is permitted (typically test infrastructure files).'))
                ->setAllowedTypes(['array'])
                ->setDefault(['DuskTestCase.php', 'TestCase.php', 'Pest.php'])
                ->getOption(),
        ]);
    }

    protected function applyFix(SplFileInfo $file, Tokens $tokens): void
    {
        foreach ($this->configuration['allow_in_files'] as $allowed) {
            if (str_ends_with($file->getPathname(), $allowed)) {
                return;
            }
        }

        foreach ($tokens as $index => $token) {
            if (! $token->isGivenKind(T_STRING)) {
                continue;
            }

            if ($token->getContent() !== 'sleep' && $token->getContent() !== 'usleep') {
                continue;
            }

            $prev = $tokens->getPrevMeaningfulToken($index);
            if ($prev !== null) {
                $prevToken = $tokens[$prev];
                if ($prevToken->isGivenKind(T_OBJECT_OPERATOR)
                    || $prevToken->getContent() === '::'
                    || $prevToken->isGivenKind(T_NEW)
                    || $prevToken->isGivenKind(T_FUNCTION)) {
                    continue;
                }
            }

            $next = $tokens->getNextMeaningfulToken($index);
            if ($next === null || $tokens[$next]->getContent() !== '(') {
                continue;
            }

            $this->report($file, $this->lineFor($tokens, $index), sprintf(
                '`%s()` is forbidden in tests — use `Carbon::setTestNow()` or semantic waits.',
                $token->getContent(),
            ));
        }
    }
}
