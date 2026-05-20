<?php

declare(strict_types=1);

use PhpCsFixer\Fixer\FixerInterface;
use PhpCsFixer\Tokenizer\Tokens;

function applyFixer(FixerInterface $fixer, string $code): string
{
    $tokens = Tokens::fromCode($code);
    $fixer->fix(new SplFileInfo('memory'), $tokens);

    return $tokens->generateCode();
}

function tryApplyFixer(FixerInterface $fixer, string $code): ?string
{
    try {
        applyFixer($fixer, $code);
    } catch (RuntimeException $exception) {
        return $exception->getMessage();
    }

    return null;
}
