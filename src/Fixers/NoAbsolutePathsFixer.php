<?php

declare(strict_types=1);

namespace Perafan\TestConventions\Fixers;

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

final class NoAbsolutePathsFixer extends AbstractTestConventionsFixer implements ConfigurableFixerInterface
{
    use ConfigurableFixerTrait;

    public function getName(): string
    {
        return 'Perafan/test_conventions_no_absolute_paths';
    }

    public function getDefinition(): FixerDefinitionInterface
    {
        return new FixerDefinition(
            'Forbid absolute filesystem paths in test code. Use `base_path()`, `storage_path()`, `Storage::fake()`.',
            [new CodeSample("<?php\n\n\$path = '/Users/me/file.txt';\n")]
        );
    }

    public function isCandidate(Tokens $tokens): bool
    {
        return $tokens->isTokenKindFound(T_CONSTANT_ENCAPSED_STRING);
    }

    protected function createConfigurationDefinition(): FixerConfigurationResolverInterface
    {
        return new FixerConfigurationResolver([
            (new FixerOptionBuilder('prefixes', 'Forbidden path prefixes inside string literals.'))
                ->setAllowedTypes(['array'])
                ->setDefault(['/Users/', '/home/'])
                ->getOption(),
        ]);
    }

    protected function applyFix(SplFileInfo $file, Tokens $tokens): void
    {
        foreach ($tokens as $index => $token) {
            if (! $token->isGivenKind(T_CONSTANT_ENCAPSED_STRING)) {
                continue;
            }

            $literal = trim($token->getContent(), "'\"");
            foreach ($this->configuration['prefixes'] as $prefix) {
                if (! str_starts_with($literal, $prefix)) {
                    continue;
                }

                $this->report($file, $this->lineFor($tokens, $index), sprintf(
                    'Absolute path forbidden: "%s" — use base_path()/storage_path()/Storage::fake().',
                    $literal,
                ));
                break;
            }
        }
    }
}
