<?php
declare(strict_types=1);

use PHPUnit\Framework\TestCase;
use SnowIO\AkeneoDataModel\AttributeValueSet;
use SnowIO\AkeneoMagento2Integration\Mapper\CustomAttributeMapper;
use SnowIO\Magento2DataModel\CustomAttribute;
use SnowIO\Magento2DataModel\CustomAttributeSet;

class CustomAttributeMapperTest extends TestCase
{
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
}
