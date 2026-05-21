<?php

declare(strict_types=1);

namespace Perafan\Pinto\Fixers;

use PhpCsFixer\FixerDefinition\CodeSample;
use PhpCsFixer\FixerDefinition\FixerDefinition;
use PhpCsFixer\FixerDefinition\FixerDefinitionInterface;
use PhpCsFixer\Tokenizer\Token;
use PhpCsFixer\Tokenizer\Tokens;
use SplFileInfo;

final class ForbiddenMatchersFixer extends AbstractPintoFixer
{
    private const REPLACEMENTS = [
        'true' => 'toBeTrue',
        'false' => 'toBeFalse',
        'null' => 'toBeNull',
    ];

    public function getName(): string
    {
        return 'Pinto/forbidden_matchers';
    }

    public function getDefinition(): FixerDefinitionInterface
    {
        return new FixerDefinition(
            'Prefer semantic matchers `toBeTrue()`, `toBeFalse()`, `toBeNull()` over generic `toBe(true)`, `toBe(false)`, `toBe(null)`.',
            [
                new CodeSample("<?php\n\nexpect(\$x)->toBe(true);\n"),
            ]
        );
    }

    public function isCandidate(Tokens $tokens): bool
    {
        return $tokens->isTokenKindFound(T_STRING);
    }

    protected function applyFix(SplFileInfo $file, Tokens $tokens): void
    {
        for ($index = 0; $index < $tokens->count(); $index++) {
            $token = $tokens[$index];
            if (! $token->isGivenKind(T_STRING) || $token->getContent() !== 'toBe') {
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

            $argIndex = $tokens->getNextMeaningfulToken($openParen);
            if ($argIndex === null) {
                continue;
            }

            $argToken = $tokens[$argIndex];
            if (! $argToken->isGivenKind(T_STRING)) {
                continue;
            }

            $argValue = strtolower($argToken->getContent());
            if (! isset(self::REPLACEMENTS[$argValue])) {
                continue;
            }

            $closeParen = $tokens->getNextMeaningfulToken($argIndex);
            if ($closeParen === null || $tokens[$closeParen]->getContent() !== ')') {
                continue;
            }

            $tokens[$index] = new Token([T_STRING, self::REPLACEMENTS[$argValue]]);

            $tokens->clearRange($openParen + 1, $closeParen - 1);
        }
    }
}
