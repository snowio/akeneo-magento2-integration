<?php
declare(strict_types=1);
namespace SnowIO\AkeneoMagento2\Test;

use PHPUnit\Framework\TestCase;
use SnowIO\AkeneoDataModel\FamilyData;
use SnowIO\AkeneoMagento2\FamilyMapper;
use SnowIO\Magento2DataModel\AttributeSet\AttributeData;
use SnowIO\Magento2DataModel\AttributeSet\AttributeGroupData;
use SnowIO\Magento2DataModel\AttributeSetData;
use SnowIO\Magento2DataModel\EntityTypeCode;

class FamilyMapperTest extends TestCase
{
    public function testMap()
    {
        $mapper = FamilyMapper::withDefaultLocale('en_GB');
        $familyData = FamilyData::fromJson([
            'code' => 'trousers',
            'labels' => [
                'en_GB' => 'Trousers',
            ],
            'attribute_groups' => [
                [
                    'code' => 'default',
                    'labels' => [
                        'en_GB' => 'Default',
                    ],
                    'sort_order' => 0,
                    'attributes' => [
                        [
                            'code' => 'sku',
                            'is_required' => [
                                'default' => true,
                            ],
                            'sort_order' => 10,
                        ],
                        [
                            'code' => 'description',
                            'is_required' => [
                                'default' => false,
                            ],
                            'sort_order' => 20,
                        ],
                    ],
                ],
                [
                    'code' => 'prices',
                    'labels' => [
                        'en_GB' => 'Prices',
                    ],
                    'sort_order' => 2,
                    'attributes' => [
                        [
                            'code' => 'rrp',
                            'is_required' => [
                                'default' => false,
                            ],
                            'sort_order' => 10,
                        ],
                        [
                            'code' => 'cost',
                            'is_required' => [
                                'default' => false,
                            ],
                            'sort_order' => 20,
                        ],
                    ],
                ],
                [
                    'code' => 'pictures',
                    'labels' => [
                        'en_GB' => 'Pictures',
                    ],
                    'sort_order' => 1,
                    'attributes' => [],
                ],
            ],
        ]);
        $expected = AttributeSetData::of(EntityTypeCode::PRODUCT, 'trousers', 'Trousers')
            ->withAttributeGroup(
                AttributeGroupData::of('default', 'Default (Akeneo)')
                    ->withSortOrder(0)
                    ->withAttribute(AttributeData::of('sku')->withSortOrder(10))
                    ->withAttribute(AttributeData::of('description')->withSortOrder(20))
            )
            ->withAttributeGroup(
                AttributeGroupData::of('prices', 'Prices (Akeneo)')
                    ->withSortOrder(2)
                    ->withAttribute(AttributeData::of('rrp')->withSortOrder(10))
                    ->withAttribute(AttributeData::of('cost')->withSortOrder(20))
            )
            ->withAttributeGroup(
                AttributeGroupData::of('pictures', 'Pictures (Akeneo)')
                    ->withSortOrder(1)
            );
        $actual = $mapper($familyData);
        self::assertTrue($expected->equals($actual));
    }
}
