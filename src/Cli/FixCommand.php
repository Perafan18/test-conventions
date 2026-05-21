<?php

declare(strict_types=1);

namespace Perafan\Pinto\Cli;

use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(name: 'fix', description: 'Apply autofixable conventions to tests.')]
final class FixCommand extends Command
{
    protected function configure(): void
    {
        $this->addArgument(
            'paths',
            InputArgument::IS_ARRAY | InputArgument::OPTIONAL,
            'Specific files or directories to fix (defaults to the paths in pinto.php).'
        );
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $packageRoot = dirname(__DIR__, 2);
        $runner = new PhpCsFixerRunner($packageRoot);

        /** @var list<string> $paths */
        $paths = (array) $input->getArgument('paths');

        $result = $runner->run(dryRun: false, extraPaths: $paths);

        if (isset($result['error'])) {
            $output->writeln('<error>'.$result['error'].'</error>');

            return self::FAILURE;
        }

        $autofixed = $result['autofixed'];
        $violations = $result['violations'];
        $cwd = getcwd() ?: null;

        if (count($autofixed) > 0) {
            $output->writeln(sprintf('<info>Applied autofixes to %d file%s:</info>', count($autofixed), count($autofixed) === 1 ? '' : 's'));
            foreach ($autofixed as $fix) {
                $file = $cwd !== null && str_starts_with($fix->file, $cwd.'/')
                    ? substr($fix->file, strlen($cwd) + 1)
                    : $fix->file;

                $rules = implode(', ', $fix->appliedRules);
                $output->writeln("  {$file} — {$rules}");
            }
            $output->writeln('');
        }

        if (count($violations) > 0) {
            $output->writeln('<comment>Remaining non-fixable violations:</comment>');
            foreach ($violations as $violation) {
                $output->writeln('  '.$violation->format($cwd));
            }
            $output->writeln('');
            $output->writeln(sprintf('<error>%d violation%s require manual fix.</error>', count($violations), count($violations) === 1 ? '' : 's'));

            return self::FAILURE;
        }

        if (count($autofixed) === 0) {
            $output->writeln('<info>All tests already respect the conventions. Nothing to fix.</info>');
        }

        return self::SUCCESS;
    }
}
