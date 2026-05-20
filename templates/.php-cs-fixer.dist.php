<?php

declare(strict_types=1);

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
    ->setRules([
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
        // 'Perafan/test_conventions_partial_mock_comment' => ['policy' => 'forbid'],
    ])
    ->setFinder(
        (new Finder())
            ->in([__DIR__.'/tests'])
            ->name('*.php')
    );
