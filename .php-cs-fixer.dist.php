<?php

declare(strict_types=1);

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
        '@PSR12' => true,
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
    ])
    ->setFinder(
        (new Finder())
            ->in([__DIR__.'/src/Tokens', __DIR__.'/tests'])
            ->notPath('Pest.php')
            ->notPath('Unit/Fixers/NoAssertTrueTrueFixerTest.php')
            ->notPath('Unit/Fixers/NoAbsolutePathsFixerTest.php')
            ->notPath('Unit/Fixers/NoPauseInBrowserFixerTest.php')
            ->notPath('Unit/Fixers/NoSleepFixerTest.php')
            ->notPath('Unit/Fixers/NoOnlyFixerTest.php')
            ->notPath('Unit/Fixers/PartialMockCommentPolicyFixerTest.php')
    );
