<?php

declare(strict_types=1);

namespace Perafan\Pinto\Fixers;

use Perafan\Pinto\Tokens\PestCallFinder;
use PhpCsFixer\Fixer\ConfigurableFixerInterface;
use PhpCsFixer\Fixer\ConfigurableFixerTrait;
use PhpCsFixer\FixerConfiguration\FixerConfigurationResolver;
use PhpCsFixer\FixerConfiguration\FixerConfigurationResolverInterface;
use PhpCsFixer\FixerConfiguration\FixerOptionBuilder;
use PhpCsFixer\FixerDefinition\CodeSample;
use PhpCsFixer\FixerDefinition\FixerDefinition;
use PhpCsFixer\FixerDefinition\FixerDefinitionInterface;
use PhpCsFixer\Tokenizer\Token;
use PhpCsFixer\Tokenizer\Tokens;
use SplFileInfo;

final class NoShouldPrefixFixer extends AbstractPintoFixer implements ConfigurableFixerInterface
{
    use ConfigurableFixerTrait;

    public function getName(): string
    {
        return 'Pinto/no_should_prefix';
    }

    public function getDefinition(): FixerDefinitionInterface
    {
        return new FixerDefinition(
            'Description in `it()`/`describe()` must not start with forbidden prefixes like `should `, `it tests `, `tests that `.',
            [
                new CodeSample("<?php\n\nit('should do X', function () {});\n"),
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
            (new FixerOptionBuilder('prefixes', 'Forbidden prefixes (case-sensitive) at the start of the description.'))
                ->setAllowedTypes(['array'])
                ->setDefault(['should ', 'it tests ', 'tests that '])
                ->getOption(),
        ]);
    }

    protected function applyFix(SplFileInfo $file, Tokens $tokens): void
    {
        $prefixes = $this->configuration['prefixes'];

        foreach (PestCallFinder::findCalls($tokens) as $call) {
            if (! $call->hasStringFirstArg()) {
                continue;
            }

            $value = (string) $call->firstStringValue;
            foreach ($prefixes as $prefix) {
                if (! str_starts_with($value, $prefix)) {
                    continue;
                }

                $newValue = substr($value, strlen($prefix));
                $newValue = ucfirst($newValue) === $newValue ? $newValue : $newValue;

                $rawToken = $tokens[$call->firstStringArgIndex];
                $quote = $rawToken->getContent()[0];
                $tokens[$call->firstStringArgIndex] = new Token([
                    T_CONSTANT_ENCAPSED_STRING,
                    $quote.$newValue.$quote,
                ]);

                break;
            }
        }
    }
}
