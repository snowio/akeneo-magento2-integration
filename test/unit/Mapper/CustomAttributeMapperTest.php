<?php

use PHPUnit\Framework\TestCase;
use SnowIO\AkeneoDataModel\AttributeValueSet;
use SnowIO\AkeneoMagento2Integration\Mapper\CustomAttributeMapper;
use SnowIO\Magento2DataModel\CustomAttribute;
use SnowIO\Magento2DataModel\CustomAttributeSet;

class CustomAttributeMapperTest extends TestCase
{

    /**
     * @dataProvider mapDataProvider
     */
    public function testMap(AttributeValueSet $akeneoAttributes, CustomAttributeSet $expected, string $currency = null)
    {
        $customAttributeMapper = CustomAttributeMapper::create($currency);
        $actual = $customAttributeMapper->map($akeneoAttributes);
        self::assertTrue($expected->equals($actual));
    }

    public function mapDataProvider()
    {
        return [
            'noCurrencySpecified' => [
                AttributeValueSet::fromJson('main', [
                    'attribute_values' => [
                        'size' => 'Large',
                        'price' => [
                            'gbp' => '30',
                            'eur' => '37.45',
                        ],
                        'weight' => '30',
                    ],
                ]),
                CustomAttributeSet::of([
                    CustomAttribute::of('size', 'Large'),
                    CustomAttribute::of('weight', '30'),
                ])
            ],
            'currencySpecified' => [
                AttributeValueSet::fromJson('main', [
                    'attribute_values' => [
                        'size' => 'Large',
                        'price' => [
                            'gbp' => '30',
                            'eur' => '37.45',
                        ],
                        'weight' => '30',
                    ],
                ]),
                CustomAttributeSet::of([
                    CustomAttribute::of('size', 'Large'),
                    CustomAttribute::of('price', '37.45'),
                    CustomAttribute::of('weight', '30'),
                ]),
                'eur',
            ],
        ];
    }

}