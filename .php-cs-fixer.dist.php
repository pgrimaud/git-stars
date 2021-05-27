<?php

$finder = (new PhpCsFixer\Finder())
    ->in(__DIR__)
    ->exclude('var');

return (new PhpCsFixer\Config())
    ->setRules([
        '@Symfony'                => true,
        'array_syntax'            => ['syntax' => 'short'],
        'concat_space'            => ['spacing' => 'one'],
        'phpdoc_var_without_name' => false,
        'binary_operator_spaces'  => ['default' => 'align_single_space_minimal'],
        'yoda_style'              => [
            'equal'            => false,
            'identical'        => false,
            'less_and_greater' => false,
        ],
    ])
    ->setFinder($finder);
