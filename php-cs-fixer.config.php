<?php

declare(strict_types=1);

$composerAutoload = __DIR__.'/../../autoload.php';
if (! class_exists(\Perafan\Pinto\Fixers\MaxDescriptionLengthFixer::class, false) && file_exists($composerAutoload)) {
    require_once $composerAutoload;
}

use PhpCsFixer\Config;
use PhpCsFixer\Finder;
use Perafan\Pinto\Fixers\ForbiddenMatchersFixer;
use Perafan\Pinto\Fixers\ItNotTestFixer;
use Perafan\Pinto\Fixers\MaxDescriptionLengthFixer;
use Perafan\Pinto\Fixers\NoAbsolutePathsFixer;
use Perafan\Pinto\Fixers\NoAppMockingFixer;
use Perafan\Pinto\Fixers\NoAssertTrueTrueFixer;
use Perafan\Pinto\Fixers\NoOnlyFixer;
use Perafan\Pinto\Fixers\NoPauseInBrowserFixer;
use Perafan\Pinto\Fixers\NoShouldPrefixFixer;
use Perafan\Pinto\Fixers\NoSleepFixer;
use Perafan\Pinto\Fixers\PartialMockCommentPolicyFixer;

$clientConfigPath = getcwd().'/pinto.php';
$clientConfig = file_exists($clientConfigPath) ? require $clientConfigPath : [];

$paths = $clientConfig['paths'] ?? ['tests'];
$notPaths = $clientConfig['allowlist'] ?? [];
$customRules = $clientConfig['rules'] ?? [];

$defaultRules = [
    'Pinto/max_description_length' => true,
    'Pinto/no_should_prefix' => true,
    'Pinto/forbidden_matchers' => true,
    'Pinto/no_app_mocking' => true,
    'Pinto/it_not_test' => true,
    'Pinto/no_assert_true_true' => true,
    'Pinto/no_pause_browser' => true,
    'Pinto/no_sleep' => true,
    'Pinto/no_only' => true,
    'Pinto/no_absolute_paths' => true,
    'Pinto/partial_mock_comment' => ['policy' => 'allow'],
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
