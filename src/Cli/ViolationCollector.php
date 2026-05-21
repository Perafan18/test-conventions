<?php

declare(strict_types=1);

namespace Perafan\Pinto\Cli;

final class ViolationCollector
{
    public const ENV_VAR = 'PINTO_VIOLATIONS_FILE';

    public static function path(): ?string
    {
        $path = getenv(self::ENV_VAR);

        return $path === false ? null : $path;
    }

    public static function add(string $file, int $line, string $ruleId, string $message): void
    {
        $path = self::path();
        if ($path === null) {
            return;
        }

        $entry = json_encode(
            ['file' => $file, 'line' => $line, 'ruleId' => $ruleId, 'message' => $message],
            JSON_THROW_ON_ERROR | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE
        );

        $handle = @fopen($path, 'ab');
        if ($handle === false) {
            return;
        }

        if (flock($handle, LOCK_EX)) {
            fwrite($handle, $entry."\n");
            fflush($handle);
            flock($handle, LOCK_UN);
        }

        fclose($handle);
    }

    /**
     * @return list<Violation>
     */
    public static function readAll(string $path): array
    {
        if (! file_exists($path)) {
            return [];
        }

        $contents = file_get_contents($path);
        if ($contents === false || $contents === '') {
            return [];
        }

        $violations = [];
        foreach (explode("\n", $contents) as $line) {
            $line = trim($line);
            if ($line === '') {
                continue;
            }

            try {
                $decoded = json_decode($line, true, flags: JSON_THROW_ON_ERROR);
            } catch (\JsonException) {
                continue;
            }

            if (! is_array($decoded) || ! isset($decoded['file'], $decoded['line'], $decoded['ruleId'], $decoded['message'])) {
                continue;
            }

            $violations[] = new Violation(
                file: (string) $decoded['file'],
                line: (int) $decoded['line'],
                ruleId: (string) $decoded['ruleId'],
                message: (string) $decoded['message'],
            );
        }

        return $violations;
    }

    public static function reset(string $path): void
    {
        if (file_exists($path)) {
            @unlink($path);
        }
    }
}
