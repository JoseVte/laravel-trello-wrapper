<?php

declare(strict_types=1);

$finder = PhpCsFixer\Finder::create()
    ->in([
        __DIR__ . '/src',
        __DIR__ . '/config',
        __DIR__ . '/tests',
    ])
    ->name('*.php')
    ->ignoreDotFiles(true)
    ->ignoreVCS(true);

return (new PhpCsFixer\Config())
    ->setRiskyAllowed(true)
    ->setRules([
        '@PSR12' => true,
        '@PHP83Migration' => true,
        'array_syntax' => ['syntax' => 'short'],
        'no_unused_imports' => true,
        'ordered_imports' => ['sort_algorithm' => 'length'],
        'single_quote' => true,
        'trailing_comma_in_multiline' => ['elements' => ['arrays']],
        'binary_operator_spaces' => ['default' => 'single_space'],
        'concat_space' => ['spacing' => 'one'],
        'no_extra_blank_lines' => true,
        'no_trailing_whitespace' => true,
        'phpdoc_align' => ['align' => 'vertical'],
        'phpdoc_separation' => true,
        'cast_spaces' => ['space' => 'single'],
    ])
    ->setFinder($finder);
