<?php

declare(strict_types=1);

namespace Perafan\Pinto\Cli;

use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(name: 'check', description: 'Check tests against the conventions without modifying files.')]
final class CheckCommand extends Command
{
    protected function configure(): void
    {
        $this->addArgument(
            'paths',
            InputArgument::IS_ARRAY | InputArgument::OPTIONAL,
            'Specific files or directories to check (defaults to the paths in pinto.php).'
        );
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $packageRoot = dirname(__DIR__, 2);
        $runner = new PhpCsFixerRunner($packageRoot);

        /** @var list<string> $paths */
        $paths = (array) $input->getArgument('paths');

        $result = $runner->run(dryRun: true, extraPaths: $paths);

        if (isset($result['error'])) {
            $output->writeln('<error>'.$result['error'].'</error>');

            return self::FAILURE;
        }

        $violations = $result['violations'];
        $autofixed = $result['autofixed'];
        $cwd = getcwd() ?: null;

        foreach ($violations as $violation) {
            $output->writeln($violation->format($cwd));
        }

        $totalAutofixable = count($autofixed);
        $totalViolations = count($violations);

        if ($totalViolations === 0 && $totalAutofixable === 0) {
            $output->writeln('<info>All tests respect the conventions.</info>');

            return self::SUCCESS;
        }

        $output->writeln('');

        if ($totalAutofixable > 0) {
            $output->writeln(sprintf(
                '<comment>%d %s autofixable violations. Run `vendor/bin/pinto fix` to apply.</comment>',
                $totalAutofixable,
                $totalAutofixable === 1 ? 'file has' : 'files have'
            ));
        }

        if ($totalViolations > 0) {
            $files = array_unique(array_map(fn (Violation $v) => $v->file, $violations));
            $fileCount = count($files);
            $output->writeln(sprintf(
                '<error>Found %d %s across %d %s.</error>',
                $totalViolations,
                $totalViolations === 1 ? 'violation' : 'violations',
                $fileCount,
                $fileCount === 1 ? 'file' : 'files'
            ));

            return self::FAILURE;
        }

        return self::FAILURE;
    }
}
