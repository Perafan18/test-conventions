<?php

declare(strict_types=1);

namespace Perafan\Pinto\Cli;

use Symfony\Component\Console\Application as BaseApplication;

final class Application extends BaseApplication
{
    public const VERSION = '2.0.3';

    public function __construct()
    {
        parent::__construct('pinto', self::VERSION);

        $this->addCommands([
            new CheckCommand(),
            new FixCommand(),
            new ListRulesCommand(),
            new InitCommand(),
        ]);
    }
}
