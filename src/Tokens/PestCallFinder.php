<?php

declare(strict_types=1);

namespace Perafan\TestConventions\Tokens;

use PhpCsFixer\Tokenizer\Tokens;

final class PestCallFinder
{
    /**
     * @return list<PestCall>
     */
    public static function findCalls(Tokens $tokens, array $names = ['it', 'test', 'describe']): array
    {
        $calls = [];

        foreach ($tokens as $index => $token) {
            if (! $token->isGivenKind(T_STRING)) {
                continue;
            }

            if (! in_array($token->getContent(), $names, true)) {
                continue;
            }

            if (! self::isTopLevelOrCallback($tokens, $index)) {
                continue;
            }

            $openParen = $tokens->getNextMeaningfulToken($index);
            if ($openParen === null || $tokens[$openParen]->getContent() !== '(') {
                continue;
            }

            $firstArg = $tokens->getNextMeaningfulToken($openParen);
            if ($firstArg === null) {
                continue;
            }

            $stringValue = null;
            if ($tokens[$firstArg]->isGivenKind(T_CONSTANT_ENCAPSED_STRING)) {
                $stringValue = self::extractStringValue($tokens[$firstArg]->getContent());
            }

            $calls[] = new PestCall(
                name: $token->getContent(),
                nameIndex: $index,
                openParenIndex: $openParen,
                firstStringArgIndex: $stringValue !== null ? $firstArg : null,
                firstStringValue: $stringValue,
            );
        }

        return $calls;
    }

    private static function isTopLevelOrCallback(Tokens $tokens, int $index): bool
    {
        $prev = $tokens->getPrevMeaningfulToken($index);
        if ($prev === null) {
            return true;
        }

        $prevContent = $tokens[$prev]->getContent();

        return $prevContent !== '->' && $prevContent !== '::' && ! $tokens[$prev]->isGivenKind(T_NEW);
    }

    private static function extractStringValue(string $rawString): string
    {
        $first = $rawString[0] ?? '';
        if ($first !== "'" && $first !== '"') {
            return $rawString;
        }

        return substr($rawString, 1, -1);
    }
}
