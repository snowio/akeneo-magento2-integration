<?php

use PHPUnit\Framework\TestCase;
use SnowIO\AkeneoDataModel\AttributeValueIdentifier;
use SnowIO\AkeneoDataModel\Scope;
use SnowIO\AkeneoMagento2Integration\Mapper\CustomAttributeMapper;
use SnowIO\AkeneoMagento2Integration\Mapper\SimpleProductMapper;
use SnowIO\AkeneoDataModel\ProductData as AkeneoProduct;
use SnowIO\Magento2DataModel\CustomAttribute;
use SnowIO\Magento2DataModel\CustomAttributeSet;
use SnowIO\Magento2DataModel\ProductData as Magento2ProductData;

class SimpleProductMapperTest extends TestCase
{

    /**
     * @dataProvider mapDataProvider
     */
    public function testMap(AkeneoProduct $akeneoProduct, Magento2ProductData $expected, SimpleProductMapper $mapper)
    {
        $actual = $mapper->map($akeneoProduct);
        self::assertTrue($actual->equals($expected));
    }

    public function mapDataProvider()
    {
        return [
            'withCustomAttributeMapperSpecified' => [
                AkeneoProduct::fromJson([
                    'sku' => 'abc123',
                    'channel' => 'main',
                    'categories' => [
                        ['mens', 't_shirts'],
                        ['mens', 'trousers'],
                    ],
                    'family' => "mens_t_shirts",
                    'attribute_values' => [
                        'size' => 'Large',
                        'price' => [
                            'gbp' => '40.48',
                            'euro' => '40.59'
                        ]
                    ],
                    'group' => null,
                    'localizations' => [],
                    'enabled' => true,
                    '@timestamp' => 1508491122,
                ]),
                Magento2ProductData::of('abc123', 'abc123')->withCustomAttributes(
                    CustomAttributeSet::of(
                        [
                            CustomAttribute::of('size', 'Large'),
                        ]
                    )
                ),
                SimpleProductMapper::create(),
            ],
            'withoutCustomAttributeMapperSpecified' => [
                AkeneoProduct::fromJson([
                    'sku' => 'abc123',
                    'channel' => 'main',
                    'categories' => [
                        ['mens', 't_shirts'],
                        ['mens', 'trousers'],
                    ],
                    'family' => "mens_t_shirts",
                    'attribute_values' => [
                        'size' => 'Large',
                        'price' => [
                            'gbp' => '40.48',
                            'euro' => '40.59'
                        ]
                    ],
                    'group' => null,
                    'localizations' => [],
                    'enabled' => true,
                    '@timestamp' => 1508491122,
                ]),
                Magento2ProductData::of('abc123', 'abc123')
                    ->withCustomAttributes(CustomAttributeSet::of([
                        CustomAttribute::of('size', 'Large'),
                        CustomAttribute::of('price', '40.48')
                    ])),
                SimpleProductMapper::create()->withCustomAttributeMapper(CustomAttributeMapper::create()
                    ->withCurrency('gbp'))
            ],
            'withCustomNameMapperSpecified' => [
                AkeneoProduct::fromJson([
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
                ]),
                Magento2ProductData::of('abc123', 'ABC 123 Product')
                    ->withCustomAttributes(CustomAttributeSet::of([
                        CustomAttribute::of('size', 'Large'),
                        CustomAttribute::of('price', '40.48'),
                        CustomAttribute::of('product_title', 'ABC 123 Product')
                    ])),
                SimpleProductMapper::create()
                    ->withCustomNameMapper(function (AkeneoProduct $productData) {
                        $productTitle = $productData
                            ->getAttributeValues()
                            ->getValue(AttributeValueIdentifier::of('product_title', Scope::ofChannel('main')));
                        return $productTitle;
                    })
                    ->withCustomAttributeMapper(CustomAttributeMapper::create()
                    ->withCurrency('gbp'))
            ],
        ];
    }
}
