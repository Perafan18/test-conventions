<?php

declare(strict_types=1);

namespace Perafan\TestConventions\Fixers;

use PhpCsFixer\FixerDefinition\CodeSample;
use PhpCsFixer\FixerDefinition\FixerDefinition;
use PhpCsFixer\FixerDefinition\FixerDefinitionInterface;
use PhpCsFixer\Tokenizer\Tokens;
use SplFileInfo;

final class NoOnlyFixer extends AbstractTestConventionsFixer
{
    public function getName(): string
    {
        return 'Perafan/test_conventions_no_only';
    }

    public function getDefinition(): FixerDefinitionInterface
    {
        return new FixerDefinition(
            'Forbid `->only()` reaching main — focus marker that should never be committed. Autofix: strip the `->only()` chained call.',
            [new CodeSample("<?php\n\nit('does X', function () {})->only();\n")]
        );
    }

    public function isCandidate(Tokens $tokens): bool
    {
        return $tokens->isTokenKindFound(T_STRING);
    }

    protected function applyFix(SplFileInfo $file, Tokens $tokens): void
    {
        for ($index = $tokens->count() - 1; $index >= 0; $index--) {
            $token = $tokens[$index];
            if (! $token->isGivenKind(T_STRING) || $token->getContent() !== 'only') {
                continue;
            }

            $prev = $tokens->getPrevMeaningfulToken($index);
            if ($prev === null || ! $tokens[$prev]->isGivenKind(T_OBJECT_OPERATOR)) {
                continue;
            }

            $openParen = $tokens->getNextMeaningfulToken($index);
            if ($openParen === null || $tokens[$openParen]->getContent() !== '(') {
                continue;
            }

            $closeParen = $tokens->getNextMeaningfulToken($openParen);
            if ($closeParen === null || $tokens[$closeParen]->getContent() !== ')') {
                continue;
            }

            $tokens->clearRange($prev, $closeParen);
        }
    }
}
