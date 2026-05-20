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
use PhpCsFixer\Tokenizer\Token;
use PhpCsFixer\Tokenizer\Tokens;
use SplFileInfo;

final class ItNotTestFixer extends AbstractTestConventionsFixer implements ConfigurableFixerInterface
{
    use ConfigurableFixerTrait;

    public function getName(): string
    {
        return 'Perafan/test_conventions_it_not_test';
    }

    public function getDefinition(): FixerDefinitionInterface
    {
        return new FixerDefinition(
            'Use `it()` instead of `test()` at top level. Autofix: rename `test(` to `it(`.',
            [new CodeSample("<?php\n\ntest('does X', function () {});\n")]
        );
    }

    public function isCandidate(Tokens $tokens): bool
    {
        return $tokens->isTokenKindFound(T_STRING);
    }

    protected function createConfigurationDefinition(): FixerConfigurationResolverInterface
    {
        return new FixerConfigurationResolver([
            (new FixerOptionBuilder('allow_in_files', 'File basenames where top-level test() is permitted (typically arch tests).'))
                ->setAllowedTypes(['array'])
                ->setDefault(['ArchTest.php'])
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
            if (! $token->isGivenKind(T_STRING) || $token->getContent() !== 'test') {
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

            $tokens[$index] = new Token([T_STRING, 'it']);
        }
    }
}
