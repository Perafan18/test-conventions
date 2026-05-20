<?php

declare(strict_types=1);

namespace Perafan\TestConventions\Cli;

use Symfony\Component\Console\Application as BaseApplication;

final class Application extends BaseApplication
{
    public const VERSION = '2.0.0';

    public function __construct()
    {
        parent::__construct('test-conventions', self::VERSION);

        $this->add(new CheckCommand());
        $this->add(new FixCommand());
        $this->add(new ListRulesCommand());
        $this->add(new InitCommand());
    }
}
