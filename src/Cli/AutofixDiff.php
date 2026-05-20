<?php

declare(strict_types=1);

namespace Perafan\TestConventions\Cli;

final readonly class AutofixDiff
{
    /**
     * @param  list<string>  $appliedRules
     */
    public function __construct(
        public string $file,
        public array $appliedRules,
        public ?string $diff,
        public bool $wouldFix,
    ) {
    }
}
