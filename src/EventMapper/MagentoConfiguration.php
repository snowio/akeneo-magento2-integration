<?php
declare(strict_types=1);
namespace SnowIO\AkeneoMagento2\EventMapper;

abstract class MagentoConfiguration
{
    public abstract function getCategoryMapper(): callable;

    public abstract function getAttributeMapper(): callable;

    public abstract function getAttributeOptionMapper(): callable;

    public abstract function getProductMapper(): callable;

    public abstract function getProductCategoryAssociationMapper(): callable;

    abstract function customAttributeIsBlacklisted(string $attributeCode): bool;
}
