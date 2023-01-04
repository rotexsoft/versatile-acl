<?php

declare(strict_types=1);

use Rector\Php74\Rector\Property\TypedPropertyRector;
use Rector\Set\ValueObject\SetList;
use Rector\Config\RectorConfig;

return static function (RectorConfig $rectorConfigurator): void {

    // get parameters
    //$parameters = $containerConfigurator->parameters();

    // Define what rule sets will be applied

    // here we can define, what sets of rules will be applied
    // tip: use "SetList" class to autocomplete sets
    $rectorConfigurator->import(SetList::PHP_52);
    $rectorConfigurator->import(SetList::PHP_53);
    $rectorConfigurator->import(SetList::PHP_54);
    $rectorConfigurator->import(SetList::PHP_55);
    $rectorConfigurator->import(SetList::PHP_56);
    $rectorConfigurator->import(SetList::PHP_70);
    $rectorConfigurator->import(SetList::PHP_71);
    $rectorConfigurator->import(SetList::PHP_72);
    $rectorConfigurator->import(SetList::PHP_73);
    $rectorConfigurator->import(SetList::PHP_74);
    //$containerConfigurator->import(SetList::PHP_80);
    //$containerConfigurator->import(SetList::PHP_81);
    $rectorConfigurator->import(SetList::DEAD_CODE);
    $rectorConfigurator->import(SetList::PSR_4);
    $rectorConfigurator->import(SetList::TYPE_DECLARATION);
    
    $rectorConfigurator->skip([
        \Rector\DeadCode\Rector\PropertyProperty\RemoveNullPropertyInitializationRector::class,
        \Rector\TypeDeclaration\Rector\ClassMethod\AddParamTypeFromPropertyTypeRector::class,
        \Rector\TypeDeclaration\Rector\FunctionLike\ReturnTypeDeclarationRector::class,
        \Rector\DeadCode\Rector\Node\RemoveNonExistingVarAnnotationRector::class,
        \Rector\DeadCode\Rector\Node\RemoveNonExistingVarAnnotationRector::class,
    ]);
    
    // get services (needed for register a single rule)
    $services = $rectorConfigurator->services();
    
    //TODO:PHP8 comment once PHP 8 becomes minimum version
    $services->remove(Rector\DeadCode\Rector\ClassMethod\RemoveUnusedPromotedPropertyRector::class);
};
