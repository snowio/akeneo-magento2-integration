<?php
declare(strict_types=1);
namespace SnowIO\AkeneoMagento2\Test\EventMapper;

use SnowIO\AkeneoMagento2\MessageMapper\ProductCategoryAssociationEventCommandMapper;
use SnowIO\Magento2DataModel\Command\SaveProductCategoryAssociationCommand;
use SnowIO\Magento2DataModel\ProductCategoryAssociation;

class ProductCategoryAssociationEventMapperTest extends CommandEventMapperTest
{

    public function testSaveCommandMapper()
    {
        $eventJson = [
            'new' => [
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
            ],
        ];

        $expected = SaveProductCategoryAssociationCommand::of(
            ProductCategoryAssociation::of('abc123', ['t_shirts', 'trousers'])
        )->withTimestamp(1508491122);
        $mapper = ProductCategoryAssociationEventCommandMapper::create($this->getMagentoConfiguration());
        $actual = $mapper->getSaveCommands($eventJson);
        self::assertEquals($expected->toJson(), iterator_to_array($actual)[0]->toJson());
    }

    public function testDeleteCommandMapper()
    {
        self::markTestSkipped('Delete command not necessary');
    }
}
