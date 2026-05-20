<?php

declare(strict_types=1);

namespace Perafan\TestConventions\Fixers;

use Perafan\TestConventions\Tokens\NamespaceResolver;
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

final class NoAppMockingFixer extends AbstractTestConventionsFixer implements ConfigurableFixerInterface
{
    use ConfigurableFixerTrait;

    private const MOCK_METHODS = ['mock', 'partialMock', 'spy'];

    public function getName(): string
    {
        return 'Perafan/test_conventions_no_app_mocking';
    }

    public function getDefinition(): FixerDefinitionInterface
    {
        return new FixerDefinition(
            'Forbid mocking classes in forbidden namespaces (default: `App\\`). Use factories or real implementations.',
            [
                new CodeSample("<?php\n\n\$this->mock(\\App\\Services\\Foo::class);\n"),
            ]
        );
    }

    public function isCandidate(Tokens $tokens): bool
    {
        return $tokens->isTokenKindFound(T_STRING);
    }

    protected function createConfigurationDefinition(): FixerConfigurationResolverInterface
    {
        return new FixerConfigurationResolver([
            (new FixerOptionBuilder('namespaces', 'Forbidden namespaces. A class is considered forbidden if its FQN starts with any of these (case-sensitive).'))
                ->setAllowedTypes(['array'])
                ->setDefault(['App\\'])
                ->getOption(),
        ]);
    }

    protected function applyFix(SplFileInfo $file, Tokens $tokens): void
    {
        foreach ($tokens as $index => $token) {
            if (! $token->isGivenKind(T_STRING)) {
                continue;
            }

            if (! in_array($token->getContent(), self::MOCK_METHODS, true)) {
                continue;
            }

            $prev = $tokens->getPrevMeaningfulToken($index);
            if ($prev === null) {
                continue;
            }

            $prevContent = $tokens[$prev]->getContent();
            $isThisMethod = $tokens[$prev]->isGivenKind(T_OBJECT_OPERATOR);
            $isMockeryStatic = $prevContent === '::'
                && $this->prevIsMockery($tokens, $prev);

            if (! $isThisMethod && ! $isMockeryStatic) {
                continue;
            }

            $openParen = $tokens->getNextMeaningfulToken($index);
            if ($openParen === null || $tokens[$openParen]->getContent() !== '(') {
                continue;
            }

            $argIndex = $tokens->getNextMeaningfulToken($openParen);
            if ($argIndex === null) {
                continue;
            }

            $fqn = NamespaceResolver::resolveClassReference($tokens, $argIndex);
            if ($fqn === null) {
                continue;
            }

            foreach ($this->configuration['namespaces'] as $forbidden) {
                if (str_starts_with($fqn, $forbidden)) {
                    $this->report($file, $this->lineFor($tokens, $index), sprintf(
                        'Cannot mock %s — class is in forbidden namespace "%s".',
                        $fqn,
                        $forbidden,
                    ));
                    break;
                }
            }
        }
    }

    private function prevIsMockery(Tokens $tokens, int $doubleColonIndex): bool
    {
        $prev = $tokens->getPrevMeaningfulToken($doubleColonIndex);
        if ($prev === null) {
            return false;
        }

        return $tokens[$prev]->isGivenKind(T_STRING)
            && $tokens[$prev]->getContent() === 'Mockery';
    }
}
