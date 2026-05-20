<?php

declare(strict_types=1);

namespace Perafan\TestConventions\Cli;

use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(name: 'init', description: 'Bootstrap test-conventions in the current project.')]
final class InitCommand extends Command
{
    protected function configure(): void
    {
        $this->addOption(
            'force',
            'f',
            InputOption::VALUE_NONE,
            'Overwrite existing files without prompting.'
        );
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $cwd = getcwd();
        if ($cwd === false) {
            $output->writeln('<error>Unable to determine current working directory.</error>');

            return self::FAILURE;
        }

        $force = (bool) $input->getOption('force');
        $packageRoot = dirname(__DIR__, 2);

        $csFixerTarget = $cwd.'/.php-cs-fixer.dist.php';
        $configTarget = $cwd.'/test-conventions.php';

        $csFixerTemplate = $packageRoot.'/templates/.php-cs-fixer.dist.php';
        $configTemplate = $packageRoot.'/templates/test-conventions.php';

        $created = [];
        $skipped = [];

        if (! file_exists($csFixerTarget) || $force) {
            copy($csFixerTemplate, $csFixerTarget);
            $created[] = '.php-cs-fixer.dist.php';
        } else {
            $skipped[] = '.php-cs-fixer.dist.php (exists; pass --force to overwrite)';
        }

        if (! file_exists($configTarget) || $force) {
            copy($configTemplate, $configTarget);
            $created[] = 'test-conventions.php';
        } else {
            $skipped[] = 'test-conventions.php (exists; pass --force to overwrite)';
        }

        foreach ($created as $file) {
            $output->writeln("<info>Created</info> {$file}");
        }
        foreach ($skipped as $file) {
            $output->writeln("<comment>Skipped</comment> {$file}");
        }

        $output->writeln('');
        $output->writeln('<info>Next steps:</info>');
        $output->writeln('  1. Edit <comment>test-conventions.php</comment> with project-specific overrides (e.g. partial_mock_comment_policy).');
        $output->writeln('  2. Add <comment>vendor/bin/test-conventions check</comment> to your CI workflow.');
        $output->writeln('  3. Optional: add a pre-commit hook running the same command.');

        return self::SUCCESS;
    }
}
