<?php

declare(strict_types=1);

namespace Perafan\Pinto\Cli;

final readonly class Violation
{
    public function __construct(
        public string $file,
        public int $line,
        public string $ruleId,
        public string $message,
    ) {
    }

    public function format(?string $cwd = null): string
    {
        $file = $this->file;
        if ($cwd !== null && str_starts_with($file, $cwd.'/')) {
            $file = substr($file, strlen($cwd) + 1);
        }

        return sprintf('%s:%d: %s %s', $file, $this->line, $this->ruleId, $this->message);
    }
}
