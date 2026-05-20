<?php

declare(strict_types=1);

namespace Perafan\TestConventions\Fixers;

use PhpCsFixer\FixerDefinition\CodeSample;
use PhpCsFixer\FixerDefinition\FixerDefinition;
use PhpCsFixer\FixerDefinition\FixerDefinitionInterface;
use PhpCsFixer\Tokenizer\Tokens;
use RuntimeException;
use SplFileInfo;

final class NoAssertTrueTrueFixer extends AbstractTestConventionsFixer
{
    public function getName(): string
    {
        return 'Perafan/test_conventions_no_assert_true_true';
    }

    public function getDefinition(): FixerDefinitionInterface
    {
        return new FixerDefinition(
            'Forbid placeholder assertions like `assertTrue(true)` or `expect(true)->toBeTrue()`. These are risky tests with no real assertion.',
            [new CodeSample("<?php\n\nassertTrue(true);\n")]
        );
    }

    public function isCandidate(Tokens $tokens): bool
    {
        return $tokens->isTokenKindFound(T_STRING);
    }

    protected function applyFix(SplFileInfo $file, Tokens $tokens): void
    {
        $code = $tokens->generateCode();

        if (preg_match('/(?<!->|::)\bassertTrue\s*\(\s*true\s*\)/', $code, $matches, PREG_OFFSET_CAPTURE)) {
            $line = substr_count(substr($code, 0, $matches[0][1]), "\n") + 1;
            throw new RuntimeException(sprintf(
                '%s:%d: %s Placeholder assertion `assertTrue(true)` is forbidden — write a real assertion.',
                $file->getPathname(),
                $line,
                $this->getName(),
            ));
        }

        if (preg_match('/expect\s*\(\s*true\s*\)\s*->\s*toBeTrue\s*\(\s*\)/', $code, $matches, PREG_OFFSET_CAPTURE)) {
            $line = substr_count(substr($code, 0, $matches[0][1]), "\n") + 1;
            throw new RuntimeException(sprintf(
                '%s:%d: %s Placeholder assertion `expect(true)->toBeTrue()` is forbidden — write a real assertion.',
                $file->getPathname(),
                $line,
                $this->getName(),
            ));
        }
    }
}
