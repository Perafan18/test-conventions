<?php

declare(strict_types=1);

namespace Perafan\TestConventions\Fixers;

use Perafan\TestConventions\Tokens\PestCallFinder;
use PhpCsFixer\Fixer\ConfigurableFixerInterface;
use PhpCsFixer\Fixer\ConfigurableFixerTrait;
use PhpCsFixer\FixerConfiguration\FixerConfigurationResolver;
use PhpCsFixer\FixerConfiguration\FixerConfigurationResolverInterface;
use PhpCsFixer\FixerConfiguration\FixerOptionBuilder;
use PhpCsFixer\FixerDefinition\CodeSample;
use PhpCsFixer\FixerDefinition\FixerDefinition;
use PhpCsFixer\FixerDefinition\FixerDefinitionInterface;
use PhpCsFixer\Tokenizer\Tokens;
use RuntimeException;
use SplFileInfo;

final class MaxDescriptionLengthFixer extends AbstractTestConventionsFixer implements ConfigurableFixerInterface
{
    use ConfigurableFixerTrait;

    public function getName(): string
    {
        return 'Perafan/test_conventions_max_description_length';
    }

    public function getDefinition(): FixerDefinitionInterface
    {
        return new FixerDefinition(
            'The first string argument of `it()`, `describe()`, and `test()` must not exceed the configured length (default 50 chars).',
            [
                new CodeSample("<?php\n\nit('does X', function () {});\n"),
                new CodeSample(
                    "<?php\n\nit('does X', function () {});\n",
                    ['limit' => 60]
                ),
            ]
        );
    }

    public function isCandidate(Tokens $tokens): bool
    {
        return $tokens->isTokenKindFound(T_STRING)
            && $tokens->isTokenKindFound(T_CONSTANT_ENCAPSED_STRING);
    }

    protected function createConfigurationDefinition(): FixerConfigurationResolverInterface
    {
        return new FixerConfigurationResolver([
            (new FixerOptionBuilder('limit', 'Maximum allowed length for the description string.'))
                ->setAllowedTypes(['int'])
                ->setDefault(50)
                ->getOption(),
        ]);
    }

    protected function applyFix(SplFileInfo $file, Tokens $tokens): void
    {
        $limit = $this->configuration['limit'];

        foreach (PestCallFinder::findCalls($tokens) as $call) {
            if (! $call->hasStringFirstArg()) {
                continue;
            }

            $length = mb_strlen((string) $call->firstStringValue);
            if ($length <= $limit) {
                continue;
            }

            $line = $this->lineFor($tokens, $call->firstStringArgIndex);

            $this->report($file, $line, sprintf(
                'Description exceeds %d chars (got %d): "%s"',
                $limit,
                $length,
                $call->firstStringValue,
            ));
        }
    }
}
