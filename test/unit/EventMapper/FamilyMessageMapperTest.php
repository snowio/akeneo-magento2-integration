<?php
declare(strict_types=1);
namespace SnowIO\AkeneoMagento2\Test\EventMapper;

use Joshdifabio\Transform\Transform;
use PHPUnit\Framework\TestCase;
use SnowIO\AkeneoMagento2\FamilyMapper;
use SnowIO\AkeneoMagento2\MessageMapper\FamilyMessageMapper;
use SnowIO\Magento2DataModel\AttributeSet\AttributeData;
use SnowIO\Magento2DataModel\AttributeSet\AttributeGroupData;
use SnowIO\Magento2DataModel\AttributeSetData;
use SnowIO\Magento2DataModel\Command\SaveAttributeSetCommand;
use SnowIO\Magento2DataModel\EntityTypeCode;

class FamilyMessageMapperTest extends TestCase
{
    /**
     * @dataProvider getTestData
     */
    public function testSaveCommandMapper(Transform $familyTransform, array $eventJson, SaveAttributeSetCommand $expectedCommand = null)
    {
        $actualCommand = FamilyMessageMapper::withFamilyTransform($familyTransform)
            ->transformAkeneoSavedEventToMagentoSaveCommands($eventJson);
        $actualCommands = iterator_to_array($actualCommand, false);
        if ($expectedCommand === null) {
            self::assertSame([], $actualCommands);
        } else {
            self::assertEquals($expectedCommand->toJson(), $actualCommands[0]->toJson());
        }
    }

    public function getTestData()
    {
        yield [
            FamilyMapper::withDefaultLocale('en_GB')->getTransform(),
            [
                'old' => null,
                'new' => ['@timestamp' => 1234] + $this->getFamilyJson()
            ],
            SaveAttributeSetCommand::of($this->getAttributeSetData())->withTimestamp(1234),
        ];

        yield [
            FamilyMapper::withDefaultLocale('en_GB')->getTransform(),
            [
                'old' => ['@timestamp' => 1233] + $this->getFamilyJson(),
                'new' => ['@timestamp' => 1234] + $this->getFamilyJson()
            ],
        ];
    }

    private function getFamilyJson(): array
    {
        return [
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
        ];
    }

    private function getAttributeSetData(): AttributeSetData
    {
        return AttributeSetData::of(EntityTypeCode::PRODUCT, 'trousers', 'Trousers')
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
    }
}
