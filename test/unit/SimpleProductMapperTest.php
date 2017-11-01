<?php
declare(strict_types=1);

use PHPUnit\Framework\TestCase;
use SnowIO\AkeneoMagento2\CustomAttributeMapper;
use SnowIO\AkeneoMagento2\SimpleProductMapper;
use SnowIO\AkeneoDataModel\ProductData as AkeneoProduct;
use SnowIO\Magento2DataModel\CustomAttribute;
use SnowIO\Magento2DataModel\CustomAttributeSet;
use SnowIO\Magento2DataModel\ProductData as Magento2ProductData;

class SimpleProductMapperTest extends TestCase
{
    public function testMap()
    {
        $akeneoProduct = $this->getAkeneoProduct();
        $mapper = SimpleProductMapper::create();
        $actual = $mapper($akeneoProduct);
        $expected = Magento2ProductData::of('abc123', 'abc123')
            ->withAttributeSetCode('mens_t_shirts')
            ->withCustomAttributes(CustomAttributeSet::of([
                CustomAttribute::of('size', 'Large'),
                CustomAttribute::of('product_title', 'ABC 123 Product')
            ]));
        self::assertTrue($actual->equals($expected));
    }

    public function testMapWithCustomMappers()
    {
        $akeneoProduct = $this->getAkeneoProduct();
        $mapper = SimpleProductMapper::create()
            ->withAttributeSetCodeMapper(function (string $akeneoFamily = 'default') {
                return "{$akeneoFamily}_modified";
            })
            ->withCustomAttributeMapper(CustomAttributeMapper::create()->withCurrency('gbp'));
        $actual = $mapper($akeneoProduct);
        $expected = Magento2ProductData::of('abc123', 'abc123')
            ->withAttributeSetCode('mens_t_shirts_modified')
            ->withCustomAttributes(CustomAttributeSet::of([
                CustomAttribute::of('size', 'Large'),
                CustomAttribute::of('price', '40.48'),
                CustomAttribute::of('product_title', 'ABC 123 Product')
            ]));
        self::assertTrue($actual->equals($expected));
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
