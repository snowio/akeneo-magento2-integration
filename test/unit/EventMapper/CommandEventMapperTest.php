<?php
declare(strict_types=1);
namespace SnowIO\AkeneoMagento2\Test\EventMapper;

use PHPUnit\Framework\TestCase;
use SnowIO\AkeneoMagento2\AttributeMapper;
use SnowIO\AkeneoMagento2\AttributeOptionMapper;
use SnowIO\AkeneoMagento2\CategoryMapper;
use SnowIO\AkeneoMagento2\MessageMapper\MagentoConfiguration;
use SnowIO\AkeneoMagento2\ProductCategoryAssociationMapper;
use SnowIO\AkeneoMagento2\ProductMapper;
use SnowIO\Magento2DataModel\ProductCategoryAssociation;

abstract class CommandEventMapperTest extends TestCase
{
    public function getMagentoConfiguration() : MagentoConfiguration
    {
        return new class extends MagentoConfiguration
        {
            public function getCategoryMapper(): callable
            {
                return CategoryMapper::create('en_GB');
            }

            public function getAttributeMapper(): callable
            {
                return AttributeMapper::create('en_GB');
            }

            public function getAttributeOptionMapper(): callable
            {
                return AttributeOptionMapper::create('en_GB');
            }

            public function getProductMapper(): callable
            {
                return ProductMapper::create();
            }

            public function getProductCategoryAssociationMapper(): callable
            {
                return ProductCategoryAssociationMapper::create();
            }

            function customAttributeIsBlacklisted(string $attributeCode): bool
            {
                return false;
            }


        };
    }

    public abstract function testSaveCommandMapper();
    public abstract function testDeleteCommandMapper();
}
