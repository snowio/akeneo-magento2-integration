<?php
declare(strict_types=1);
namespace SnowIO\AkeneoMagento2\Test;

use PHPUnit\Framework\TestCase;
use SnowIO\AkeneoDataModel\ProductData as AkeneoProduct;
use SnowIO\AkeneoMagento2\ProductCategoryAssociationMapper;
use SnowIO\Magento2DataModel\ProductCategoryAssociation;

class ProductCategoryAssociationMapperTest extends TestCase
{
    public function testMap()
    {
        $mapper = ProductCategoryAssociationMapper::create();
        $productData = $this->getAkeneoProduct();
        $expectedProductCategoryAssociation = ProductCategoryAssociation::of('abc123', ['t_shirts', 'trousers']);
        $actualProductCategoryAssociation = $mapper($productData);
        self::assertTrue($expectedProductCategoryAssociation->equals($actualProductCategoryAssociation));
    }

    private function getAkeneoProduct(): AkeneoProduct
    {
        return AkeneoProduct::fromJson([
            'sku' => 'abc123',
            'channel' => 'main',
            'categories' => [
                ['mens', 't_shirts'],
                ['mens', 'trousers'],
            ],
            'family' => "mens_t_shirts",
            'attribute_values' => [
                'size' => 'Large',
                'product_title' => 'ABC 123 Product',
                'price' => [
                    'gbp' => '40.48',
                    'euro' => '40.59'
                ]
            ],
            'group' => null,
            'localizations' => [],
            'enabled' => true,
            '@timestamp' => 1508491122,
        ]);
    }
}