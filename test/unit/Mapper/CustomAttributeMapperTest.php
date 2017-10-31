<?php
declare(strict_types=1);

use PHPUnit\Framework\TestCase;
use SnowIO\AkeneoDataModel\AttributeValueSet;
use SnowIO\AkeneoMagento2Integration\Mapper\CustomAttributeMapper;
use SnowIO\Magento2DataModel\CustomAttribute;
use SnowIO\Magento2DataModel\CustomAttributeSet;

class CustomAttributeMapperTest extends TestCase
{

    public function testMap()
    {
        $akeneoAttributes = AttributeValueSet::fromJson('main', [
            'attribute_values' => [
                'size' => 'Large',
                'price' => [
                    'gbp' => '30',
                    'eur' => '37.45',
                ],
                'weight' => '30',
            ],
        ]);
        $customAttributeMapper = CustomAttributeMapper::create()->withCurrency('eur');
        $expected = CustomAttributeSet::of([
            CustomAttribute::of('size', 'Large'),
            CustomAttribute::of('price', '37.45'),
            CustomAttribute::of('weight', '30'),
        ]);
        $actual = $customAttributeMapper->map($akeneoAttributes);
        self::assertTrue($expected->equals($actual));
    }
}
