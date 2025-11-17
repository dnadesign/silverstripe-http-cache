<?php

declare(strict_types=1);

use PhpCsFixer\Fixer\Operator\NotOperatorWithSuccessorSpaceFixer;
use Symplify\EasyCodingStandard\Config\ECSConfig;

return ECSConfig::configure()
    ->withPaths([
        __DIR__ . '/extensions',
    ])
    ->withPreparedSets(common: true, psr12: true)
    ->withSkip([
        NotOperatorWithSuccessorSpaceFixer::class,
    ]);
