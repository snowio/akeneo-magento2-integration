<?php
declare(strict_types=1);
namespace SnowIO\AkeneoMagento2\Test\EventMapper;

use SnowIO\AkeneoMagento2\EventMapper\ProductEventCommandMapper;
use SnowIO\Magento2DataModel\Command\DeleteProductCommand;
use SnowIO\Magento2DataModel\Command\SaveProductCommand;
use SnowIO\Magento2DataModel\CustomAttribute;
use SnowIO\Magento2DataModel\CustomAttributeSet;
use SnowIO\Magento2DataModel\ProductData;

class ProductCommandEventMapper extends CommandEventMapperTest
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
            'old' => null
        ];

        $expected = SaveProductCommand::of(ProductData::of('abc123', 'abc123')
            ->withAttributeSetCode('mens_t_shirts')
            ->withCustomAttributes(CustomAttributeSet::of([
                CustomAttribute::of('size', 'Large'),
                CustomAttribute::of('product_title', 'ABC 123 Product')
            ])))
            ->withTimestamp(1508491122);
        $mapper = ProductEventCommandMapper::create($this->getMagentoConfiguration());
        $actual = $mapper->getSaveCommands($eventJson);
        self::assertEquals($expected->toJson(), iterator_to_array($actual)[0]->toJson());
    }

    public function testDeleteCommandMapper()
    {
        $eventJson = [
            'old' => [
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
            'new' => []
        ];
        $expected = DeleteProductCommand::of('abc123')->withTimestamp(1508491122);
        $mapper = ProductEventCommandMapper::create($this->getMagentoConfiguration());
        $actual = $mapper->getDeleteCommands($eventJson);
        self::assertEquals($expected->toJson(), iterator_to_array($actual)[0]->toJson());
    }
}