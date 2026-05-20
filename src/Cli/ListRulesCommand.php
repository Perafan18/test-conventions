<?php

declare(strict_types=1);

namespace Perafan\TestConventions\Cli;

use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(name: 'list-rules', description: 'List all conventions rules distributed by this package.')]
final class ListRulesCommand extends Command
{
    /**
     * @return list<array{0: string, 1: string, 2: string, 3: string, 4: string}>
     */
    private function rules(): array
    {
        return [
            ['R01', 'Perafan/test_conventions_it_not_test', '§2.1', 'autofix', 'Use it() instead of test() at top level.'],
            ['R02', 'Perafan/test_conventions_max_description_length', '§2.2', 'detect', 'Description ≤ 50 chars.'],
            ['R03', 'Perafan/test_conventions_no_should_prefix', '§2.2', 'autofix', 'No should / it tests / tests that prefix.'],
            ['R04', 'Perafan/test_conventions_forbidden_matchers', '§4.2', 'autofix', 'No toBe(true|false|null) — use semantic matchers.'],
            ['R05', 'Perafan/test_conventions_no_assert_true_true', '§8.3', 'detect', 'No placeholder assertTrue(true) / expect(true)->toBeTrue().'],
            ['R06', 'Perafan/test_conventions_no_app_mocking', '§5.1', 'detect', 'No mocking App\\ — use factories or real services.'],
            ['R07', 'Perafan/test_conventions_no_pause_browser', '§7.3', 'detect', 'No ->pause()/->wait() with fixed timeouts in Browser.'],
            ['R08', 'Perafan/test_conventions_no_sleep', '§8.5', 'detect', 'No sleep()/usleep() in tests.'],
            ['R09', 'Perafan/test_conventions_no_only', '§8.12', 'autofix', 'No ->only() reaching main.'],
            ['R11', 'Perafan/test_conventions_no_absolute_paths', '§8.13', 'detect', 'No /Users/ or /home/ absolute paths.'],
            ['§5.3', 'Perafan/test_conventions_partial_mock_comment', '§5.3', 'detect', 'partial_mock_comment_policy: forbid/require/allow.'],
        ];
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $table = new Table($output);
        $table->setHeaders(['#', 'Rule', 'Section', 'Mode', 'Description']);

        foreach ($this->rules() as $row) {
            $table->addRow($row);
        }

        $table->render();

        $output->writeln('');
        $output->writeln('<comment>Code-review-only (NOT mechanized): R10 (no try/catch in test bodies), R12 (inserts via helpers).</comment>');

        return self::SUCCESS;
    }
}
