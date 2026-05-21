<?php

declare(strict_types=1);

namespace Perafan\Pinto\Cli;

use Symfony\Component\Process\Process;

final class PhpCsFixerRunner
{
    public function __construct(private readonly string $packageRoot)
    {
    }

    /**
     * @param  list<string>  $extraPaths
     * @return array{exitCode: int, violations: list<Violation>, autofixed: list<AutofixDiff>, error?: string}
     */
    public function run(bool $dryRun, array $extraPaths = []): array
    {
        $binary = $this->phpCsFixerBinary();
        if ($binary === null) {
            return [
                'exitCode' => 127,
                'violations' => [],
                'autofixed' => [],
                'error' => 'php-cs-fixer binary not found. Make sure friendsofphp/php-cs-fixer is installed.',
            ];
        }

        $configPath = $this->packageRoot.'/php-cs-fixer.config.php';
        $violationsFile = tempnam(sys_get_temp_dir(), 'pinto-');
        if ($violationsFile === false) {
            return [
                'exitCode' => 1,
                'violations' => [],
                'autofixed' => [],
                'error' => 'Unable to allocate a temporary file for the violation collector.',
            ];
        }

        $args = [
            $binary,
            'fix',
            '--config='.$configPath,
            '--using-cache=no',
            '--show-progress=none',
            '--format=json',
        ];

        if ($dryRun) {
            $args[] = '--dry-run';
            $args[] = '--diff';
        }

        if ($extraPaths !== []) {
            $args = array_merge($args, $extraPaths);
        }

        try {
            $process = new Process($args, getcwd() ?: null, [
                ViolationCollector::ENV_VAR => $violationsFile,
            ]);
            $process->setTimeout(null);
            $process->run();

            $stdout = $process->getOutput();

            $violations = ViolationCollector::readAll($violationsFile);
            $autofixed = $this->parseAutofixed($stdout, $dryRun);

            return [
                'exitCode' => $process->getExitCode() ?? 1,
                'violations' => $violations,
                'autofixed' => $autofixed,
            ];
        } finally {
            ViolationCollector::reset($violationsFile);
        }
    }

    /**
     * @return list<AutofixDiff>
     */
    private function parseAutofixed(string $stdout, bool $dryRun): array
    {
        $autofixed = [];
        $decoded = json_decode($stdout, true);
        if (! is_array($decoded) || ! isset($decoded['files']) || ! is_array($decoded['files'])) {
            return [];
        }

        foreach ($decoded['files'] as $file) {
            if (! is_array($file) || ! isset($file['name'])) {
                continue;
            }
            $autofixed[] = new AutofixDiff(
                file: $file['name'],
                appliedRules: $file['appliedFixers'] ?? [],
                diff: $file['diff'] ?? null,
                wouldFix: $dryRun,
            );
        }

        return $autofixed;
    }

    private function phpCsFixerBinary(): ?string
    {
        $cwd = getcwd();
        $candidates = array_filter([
            $cwd !== false ? $cwd.'/vendor/bin/php-cs-fixer' : null,
            $this->packageRoot.'/vendor/bin/php-cs-fixer',
        ]);

        foreach ($candidates as $candidate) {
            if (file_exists($candidate)) {
                return $candidate;
            }
        }

        return null;
    }
}
