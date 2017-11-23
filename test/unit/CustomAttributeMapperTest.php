<?php
declare(strict_types=1);

namespace SnowIO\AkeneoMagento2\Test;

use PHPUnit\Framework\TestCase;
use SnowIO\AkeneoDataModel\AttributeValue;
use SnowIO\AkeneoDataModel\AttributeValueSet;
use SnowIO\AkeneoMagento2\CustomAttributeMapper;
use SnowIO\Magento2DataModel\CustomAttribute;
use SnowIO\Magento2DataModel\CustomAttributeSet;

class CustomAttributeMapperTest extends TestCase
{
    /** @var AttributeValueSet */
    private $akeneoAttributes;

    public function setUp()
    {
        $this->akeneoAttributes = AttributeValueSet::fromJson('main', [
            'attribute_values' => [
                'size' => 'Large',
                'price' => [
                    'gbp' => '30',
                    'eur' => '37.45',
                ],
                'weight' => '30',
            ],
        ]);
    }

    public function testMap()
    {
        $customAttributeMapper = CustomAttributeMapper::create()->withCurrency('eur');
        $expected = CustomAttributeSet::of([
            CustomAttribute::of('size', 'Large'),
            CustomAttribute::of('price', '37.45'),
            CustomAttribute::of('weight', '30'),
        ]);
        $actual = $customAttributeMapper($this->akeneoAttributes);
        self::assertTrue($expected->equals($actual));
    }

    public function testMapWithoutCurrency()
    {
        $customAttributeMapper = CustomAttributeMapper::create();
        $expected = CustomAttributeSet::of([
            CustomAttribute::of('size', 'Large'),
            CustomAttribute::of('weight', '30'),
        ]);
        $actual = $customAttributeMapper($this->akeneoAttributes);
        self::assertTrue($expected->equals($actual));
    }

    public function testMapWithInputFilter()
    {
        $customAttributeMapper = CustomAttributeMapper::create()
            ->withInputFilter(function (AttributeValue $attributeValue) {
            return !\in_array($attributeValue->getAttributeCode(), ['sku', 'url_key', 'visibility']);
        });

        $akeneoAttributeWithBlacklistedAttribute = $this->akeneoAttributes
            ->add(
                AttributeValueSet::fromJson('main', [
                    'attribute_values' => [
                        'sku' => 'snowio-test-product',
                        'url_key' => 'snowio-test-product_url',
                        'visibility' => 'catalog_search',
                    ]
                ])
            );

        $expected = CustomAttributeSet::of([
            CustomAttribute::of('size', 'Large'),
            CustomAttribute::of('weight', '30'),
        ]);
        $actual = $customAttributeMapper($akeneoAttributeWithBlacklistedAttribute);
        self::assertTrue($expected->equals($actual));
    }

    public function testMapWithAttributeCodeMapper()
    {
        $customAttributeMapper = CustomAttributeMapper::create()
            ->withAttributeCodeMapper(
                function (string $attributeCode) {
                    if (0 === \strpos($attributeCode, 'vg_')) {
                        $attributeCode = \substr($attributeCode, 3);
                    }

                    return $attributeCode;
                });

        $akeneoAttribute = $this->akeneoAttributes
            ->add(
                AttributeValueSet::fromJson('main', [
                    'attribute_values' => [
                        'vg_volume' => '12',
                    ],
                ])
            );

        $expected = CustomAttributeSet::of([
            CustomAttribute::of('size', 'Large'),
            CustomAttribute::of('weight', '30'),
            CustomAttribute::of('volume', '12'),
        ]);
        $actual = $customAttributeMapper($akeneoAttribute);
        self::assertTrue($expected->equals($actual));
    }
}
