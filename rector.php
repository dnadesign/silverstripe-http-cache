<?php

declare(strict_types=1);

use Cambis\SilverstripeRector\Set\ValueObject\SilverstripeLevelSetList;
use Cambis\SilverstripeRector\Set\ValueObject\SilverstripeSetList;
use Cambis\SilverstripeRector\Silverstripe52\Rector\Class_\AddExtendsAnnotationToExtensionRector;
use Rector\Config\RectorConfig;

return RectorConfig::configure()
    ->withImportNames()
    ->withPaths([
        __DIR__ . '/extensions',
    ])
    ->withPreparedSets(
        deadCode: true,
        typeDeclarations: true
    )
    ->withPhpSets()
    ->withSets([
        SilverstripeLevelSetList::UP_TO_SILVERSTRIPE_60,
        SilverstripeSetList::CODE_QUALITY,
    ])
    ->withSkip([
        AddExtendsAnnotationToExtensionRector::class,
    ]);
