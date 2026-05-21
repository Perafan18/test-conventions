<?php

declare(strict_types=1);

namespace Perafan\Pinto\Fixers;

use PhpCsFixer\AbstractFixer;
use PhpCsFixer\Tokenizer\Tokens;

abstract class AbstractPintoFixer extends AbstractFixer
{
    public function getPriority(): int
    {
        return 0;
    }

    public function isRisky(): bool
    {
        return false;
    }

    protected function shortName(): string
    {
        $parts = explode('\\', static::class);
        $class = end($parts);

        return substr($class, 0, -strlen('Fixer'));
    }

    protected function lineFor(Tokens $tokens, int $index): int
    {
        $line = 1;
        for ($i = 0; $i < $index; $i++) {
            $line += substr_count($tokens[$i]->getContent(), "\n");
        }

        return $line;
    }

    protected function report(\SplFileInfo $file, int $line, string $message): void
    {
        if (\Perafan\Pinto\Cli\ViolationCollector::path() !== null) {
            \Perafan\Pinto\Cli\ViolationCollector::add(
                $file->getPathname(),
                $line,
                $this->getName(),
                $message,
            );

            return;
        }

        throw new \RuntimeException(sprintf(
            '%s:%d: %s %s',
            $file->getPathname(),
            $line,
            $this->getName(),
            $message,
        ));
    }
}
