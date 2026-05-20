<?php

declare(strict_types=1);

use PhpCsFixer\Config;
use PhpCsFixer\Finder;
use Perafan\TestConventions\Fixers\ForbiddenMatchersFixer;
use Perafan\TestConventions\Fixers\MaxDescriptionLengthFixer;
use Perafan\TestConventions\Fixers\NoAppMockingFixer;
use Perafan\TestConventions\Fixers\NoShouldPrefixFixer;

return (new Config())
    ->setRiskyAllowed(false)
    ->registerCustomFixers([
        new MaxDescriptionLengthFixer(),
        new NoShouldPrefixFixer(),
        new ForbiddenMatchersFixer(),
        new NoAppMockingFixer(),
    ])
    ->setRules([
        '@PSR12' => true,
        'Perafan/test_conventions_max_description_length' => true,
        'Perafan/test_conventions_no_should_prefix' => true,
        'Perafan/test_conventions_forbidden_matchers' => true,
        'Perafan/test_conventions_no_app_mocking' => true,
    ])
    ->setFinder(
        (new Finder())
            ->in([__DIR__.'/src', __DIR__.'/tests'])
            ->exclude(['Fixtures'])
    );
