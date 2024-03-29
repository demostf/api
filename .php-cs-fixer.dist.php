<?php

$finder = PhpCsFixer\Finder::create()
    ->exclude('vendor')
    ->in(__DIR__);


$config = new PhpCsFixer\Config();
return $config->setRules([
        '@PSR2' => true,
        '@Symfony' => true,
        '@Symfony:risky' => true,
        'strict_param' => true,
        'array_syntax' => ['syntax' => 'short'],
        'braces' => ['position_after_functions_and_oop_constructs' => 'same'],
        'declare_strict_types' => true,
        'concat_space' => ['spacing' => 'one'],
        'ternary_to_null_coalescing' => true,
        'phpdoc_order' => true,
        'visibility_required' => true,
    ])
    ->setFinder($finder);
