<?php
declare(strict_types=1);
namespace SnowIO\AkeneoMagento2\Test\EventMapper;

use SnowIO\AkeneoMagento2\EventMapper\AttributeOptionEventCommandMapper;
use SnowIO\Magento2DataModel\AttributeOption;
use SnowIO\Magento2DataModel\Command\DeleteAttributeOptionCommand;
use SnowIO\Magento2DataModel\Command\SaveAttributeOptionCommand;

class AttributeOptionCommandEventMapperTest extends CommandEventMapperTest
{
    public function testSaveCommandMapper()
    {
        $eventJson = [
            'new' => [
                '@timestamp' => 1510313694,
                'attribute' => 'size',
                'code' => 'large',
                'labels' => [
                    'en_GB' => 'Large'
                ],
            ],
        ];

        $expected = SaveAttributeOptionCommand::of(AttributeOption::of('size', 'large', 'Large'))
            ->withTimestamp(1510313694);
        $mapper = AttributeOptionEventCommandMapper::create($this->getMagentoConfiguration());
        $actual = $mapper->getSaveCommands($eventJson);
        self::assertEquals($expected->toJson(), iterator_to_array($actual)[0]->toJson());
    }

    public function testDeleteCommandMapper()
    {
        $eventJson = [
            '@timestamp' => 1510313694,
            'attribute' => 'size',
            'code' => 'large',
            'labels' => [
                'en_GB' => 'Large'
            ],
        ];

        $expected = DeleteAttributeOptionCommand::of('size', 'large')
            ->withTimestamp(1510313694);
        $mapper = AttributeOptionEventCommandMapper::create($this->getMagentoConfiguration());
        $actual = $mapper->getDeleteCommands($eventJson);
        self::assertEquals($expected->toJson(), iterator_to_array($actual)[0]->toJson());
    }
}
