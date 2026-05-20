<?php

declare(strict_types=1);

return [
    'paths' => [
        'tests',
    ],

    'allowlist' => [
        // Paths or substrings to exclude from the Finder. Use sparingly,
        // typically only for pre-existing violations that have not been fixed yet.
        // Example: 'tests/Unit/ArchTest.php',
    ],

    'rules' => [
        // Override default rule configurations. Only include the rules you want to customise.
        // The defaults activate all 11 fixers; rules omitted here keep their defaults.

        // 'Perafan/test_conventions_max_description_length' => ['limit' => 50],

        // §5.3 conflict — pick the posture matching your project's comments policy:
        //
        // 'forbid'  — no inline comment above partialMock(). If you would write one,
        //             extract an explicit Fake class instead.
        // 'require' — inline comment is required (the project's documented exception).
        // 'allow'   — no enforcement either way (default).
        //
        // 'Perafan/test_conventions_partial_mock_comment' => ['policy' => 'forbid'],
    ],
];
