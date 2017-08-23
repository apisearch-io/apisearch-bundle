<?php

$finder = PhpCsFixer\Finder::create()
    ->exclude('vendor')
    ->exclude('web')
    ->exclude('bin')
    ->exclude('var')
    ->in(__DIR__)
;

return PhpCsFixer\Config::create()
    ->setRules([
        '@Symfony' => true,
    ])
    ->setFinder($finder)
;