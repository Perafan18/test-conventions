<?php

declare(strict_types=1);

namespace Perafan\Pinto\Tokens;

use PhpCsFixer\Tokenizer\Tokens;

final class NamespaceResolver
{
    public static function resolveClassReference(Tokens $tokens, int $startIndex): ?string
    {
        $token = $tokens[$startIndex];

        if ($token->isGivenKind(T_CONSTANT_ENCAPSED_STRING)) {
            $literal = trim($token->getContent(), "'\"");

            return self::resolveAlias($tokens, $literal);
        }

        $parts = [];
        $index = $startIndex;
        $isAbsolute = false;

        if ($tokens[$index]->isGivenKind(T_NS_SEPARATOR)) {
            $isAbsolute = true;
            $index = $tokens->getNextMeaningfulToken($index);
            if ($index === null) {
                return null;
            }
        }

        while ($index !== null && $tokens[$index]->isGivenKind([T_STRING, T_NAME_QUALIFIED, T_NAME_FULLY_QUALIFIED])) {
            $parts[] = $tokens[$index]->getContent();

            $next = $tokens->getNextMeaningfulToken($index);
            if ($next === null || ! $tokens[$next]->isGivenKind(T_NS_SEPARATOR)) {
                break;
            }

            $index = $tokens->getNextMeaningfulToken($next);
        }

        if ($parts === []) {
            return null;
        }

        $fqn = implode('\\', $parts);

        if ($isAbsolute) {
            return ltrim($fqn, '\\');
        }

        return self::resolveAlias($tokens, $fqn);
    }

    private static function resolveAlias(Tokens $tokens, string $name): string
    {
        $head = explode('\\', $name)[0];
        $tail = substr($name, strlen($head));

        $aliasMap = self::collectUseAliases($tokens);

        if (isset($aliasMap[$head])) {
            return $aliasMap[$head].$tail;
        }

        $namespace = self::collectFileNamespace($tokens);
        if ($namespace !== null) {
            return $namespace.'\\'.$name;
        }

        return ltrim($name, '\\');
    }

    /**
     * @return array<string, string>
     */
    private static function collectUseAliases(Tokens $tokens): array
    {
        $aliases = [];

        foreach ($tokens as $index => $token) {
            if (! $token->isGivenKind(T_USE)) {
                continue;
            }

            $nameStart = $tokens->getNextMeaningfulToken($index);
            if ($nameStart === null) {
                continue;
            }

            $fqn = '';
            $aliasName = null;
            $cursor = $nameStart;

            while ($cursor !== null) {
                $current = $tokens[$cursor];

                if ($current->getContent() === ';' || $current->getContent() === ',') {
                    break;
                }

                if ($current->isGivenKind(T_AS)) {
                    $aliasIndex = $tokens->getNextMeaningfulToken($cursor);
                    if ($aliasIndex !== null) {
                        $aliasName = $tokens[$aliasIndex]->getContent();
                    }
                    break;
                }

                if ($current->isGivenKind([T_STRING, T_NAME_QUALIFIED, T_NAME_FULLY_QUALIFIED])) {
                    $fqn .= $current->getContent();
                } elseif ($current->isGivenKind(T_NS_SEPARATOR)) {
                    $fqn .= '\\';
                }

                $cursor = $tokens->getNextMeaningfulToken($cursor);
            }

            $fqn = ltrim($fqn, '\\');
            if ($fqn === '') {
                continue;
            }

            $key = $aliasName ?? self::lastPart($fqn);
            $aliases[$key] = $fqn;
        }

        return $aliases;
    }

    private static function collectFileNamespace(Tokens $tokens): ?string
    {
        foreach ($tokens as $index => $token) {
            if (! $token->isGivenKind(T_NAMESPACE)) {
                continue;
            }

            $nameIndex = $tokens->getNextMeaningfulToken($index);
            if ($nameIndex === null) {
                continue;
            }

            $namespace = '';
            $cursor = $nameIndex;
            while ($cursor !== null) {
                $current = $tokens[$cursor];
                if ($current->getContent() === ';' || $current->getContent() === '{') {
                    break;
                }
                $namespace .= $current->getContent();
                $cursor = $tokens->getNextMeaningfulToken($cursor);
            }

            $namespace = trim($namespace);
            if ($namespace !== '') {
                return ltrim($namespace, '\\');
            }
        }

        return null;
    }

    private static function lastPart(string $fqn): string
    {
        $parts = explode('\\', $fqn);

        return end($parts) ?: $fqn;
    }
}
