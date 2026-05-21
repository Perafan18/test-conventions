<?php

declare(strict_types=1);

$composerAutoload = __DIR__.'/../../autoload.php';
if (! class_exists(\Perafan\TestConventions\Fixers\MaxDescriptionLengthFixer::class, false) && file_exists($composerAutoload)) {
    require_once $composerAutoload;
}

use PhpCsFixer\Config;
use PhpCsFixer\Finder;
use Perafan\TestConventions\Fixers\ForbiddenMatchersFixer;
use Perafan\TestConventions\Fixers\ItNotTestFixer;
use Perafan\TestConventions\Fixers\MaxDescriptionLengthFixer;
use Perafan\TestConventions\Fixers\NoAbsolutePathsFixer;
use Perafan\TestConventions\Fixers\NoAppMockingFixer;
use Perafan\TestConventions\Fixers\NoAssertTrueTrueFixer;
use Perafan\TestConventions\Fixers\NoOnlyFixer;
use Perafan\TestConventions\Fixers\NoPauseInBrowserFixer;
use Perafan\TestConventions\Fixers\NoShouldPrefixFixer;
use Perafan\TestConventions\Fixers\NoSleepFixer;
use Perafan\TestConventions\Fixers\PartialMockCommentPolicyFixer;

$clientConfigPath = getcwd().'/test-conventions.php';
$clientConfig = file_exists($clientConfigPath) ? require $clientConfigPath : [];

$paths = $clientConfig['paths'] ?? ['tests'];
$notPaths = $clientConfig['allowlist'] ?? [];
$customRules = $clientConfig['rules'] ?? [];

$defaultRules = [
    'Perafan/test_conventions_max_description_length' => true,
    'Perafan/test_conventions_no_should_prefix' => true,
    'Perafan/test_conventions_forbidden_matchers' => true,
    'Perafan/test_conventions_no_app_mocking' => true,
    'Perafan/test_conventions_it_not_test' => true,
    'Perafan/test_conventions_no_assert_true_true' => true,
    'Perafan/test_conventions_no_pause_browser' => true,
    'Perafan/test_conventions_no_sleep' => true,
    'Perafan/test_conventions_no_only' => true,
    'Perafan/test_conventions_no_absolute_paths' => true,
    'Perafan/test_conventions_partial_mock_comment' => ['policy' => 'allow'],
];

$finder = (new Finder())
    ->name('*.php');

$absolutePaths = array_map(
    fn (string $path): string => str_starts_with($path, '/') ? $path : getcwd().'/'.$path,
    $paths
);
$finder->in($absolutePaths);

foreach ($notPaths as $notPath) {
    $finder->notPath($notPath);
}

return (new Config())
    ->setRiskyAllowed(false)
    ->registerCustomFixers([
        new MaxDescriptionLengthFixer(),
        new NoShouldPrefixFixer(),
        new ForbiddenMatchersFixer(),
        new NoAppMockingFixer(),
        new ItNotTestFixer(),
        new NoAssertTrueTrueFixer(),
        new NoPauseInBrowserFixer(),
        new NoSleepFixer(),
        new NoOnlyFixer(),
        new NoAbsolutePathsFixer(),
        new PartialMockCommentPolicyFixer(),
    ])
    ->setRules(array_merge($defaultRules, $customRules))
    ->setFinder($finder);
