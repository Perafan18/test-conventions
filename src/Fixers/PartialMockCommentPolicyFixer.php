<?php

declare(strict_types=1);

namespace Perafan\Pinto\Fixers;

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

final class PartialMockCommentPolicyFixer extends AbstractPintoFixer implements ConfigurableFixerInterface
{
    use ConfigurableFixerTrait;

    public const POLICY_FORBID = 'forbid';

    public const POLICY_REQUIRE = 'require';

    public const POLICY_ALLOW = 'allow';

    public function getName(): string
    {
        return 'Pinto/partial_mock_comment';
    }

    public function getDefinition(): FixerDefinitionInterface
    {
        return new FixerDefinition(
            'Enforce or forbid an inline comment above a `$this->partialMock(...)` call. Resolves the §5.3 split between projects: comments policy of the repo determines the setting.',
            [
                new CodeSample("<?php\n\n\$this->partialMock(Foo::class);\n", ['policy' => self::POLICY_FORBID]),
                new CodeSample("<?php\n\n\$this->partialMock(Foo::class);\n", ['policy' => self::POLICY_REQUIRE]),
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
            (new FixerOptionBuilder('policy', 'Policy for inline comments above partialMock() calls: forbid, require, or allow.'))
                ->setAllowedValues([self::POLICY_FORBID, self::POLICY_REQUIRE, self::POLICY_ALLOW])
                ->setDefault(self::POLICY_ALLOW)
                ->getOption(),
        ]);
    }

    protected function applyFix(SplFileInfo $file, Tokens $tokens): void
    {
        $policy = $this->configuration['policy'];

        if ($policy === self::POLICY_ALLOW) {
            return;
        }

        foreach ($tokens as $index => $token) {
            if (! $token->isGivenKind(T_STRING) || $token->getContent() !== 'partialMock') {
                continue;
            }

            $prev = $tokens->getPrevMeaningfulToken($index);
            if ($prev === null || ! $tokens[$prev]->isGivenKind(T_OBJECT_OPERATOR)) {
                continue;
            }

            $hasComment = $this->hasCommentImmediatelyAbove($tokens, $index);

            if ($policy === self::POLICY_FORBID && $hasComment) {
                $this->report($file, $this->lineFor($tokens, $index), 'Inline comment above partialMock() is forbidden by project policy. Extract an explicit Fake class instead.');
            }

            if ($policy === self::POLICY_REQUIRE && ! $hasComment) {
                $this->report($file, $this->lineFor($tokens, $index), 'Inline comment above partialMock() is required — document the reason.');
            }
        }
    }

    private function hasCommentImmediatelyAbove(Tokens $tokens, int $partialMockIndex): bool
    {
        $statementStart = $partialMockIndex;
        for ($i = $partialMockIndex - 1; $i >= 0; $i--) {
            $content = $tokens[$i]->getContent();
            if (str_contains($content, "\n")) {
                $statementStart = $i + 1;
                break;
            }
        }

        for ($i = $statementStart - 1; $i >= 0; $i--) {
            $token = $tokens[$i];
            if ($token->isWhitespace()) {
                if (substr_count($token->getContent(), "\n") > 1) {
                    return false;
                }
                continue;
            }
            if ($token->isComment()) {
                return true;
            }

            return false;
        }

        return false;
    }
}
