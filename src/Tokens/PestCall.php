<?php

declare(strict_types=1);

namespace Perafan\TestConventions\Tokens;

final readonly class PestCall
{
    public function __construct(
        public string $name,
        public int $nameIndex,
        public int $openParenIndex,
        public ?int $firstStringArgIndex,
        public ?string $firstStringValue,
    ) {
    }

    public function hasStringFirstArg(): bool
    {
        return $this->firstStringArgIndex !== null;
    }
}
