<?php
declare(strict_types = 1);
namespace SnowIO\AkeneoMagento2\Test\EventMapper;

use SnowIO\AkeneoMagento2\MessageMapper\AttributeMessageMapper;
use SnowIO\Magento2DataModel\AttributeData;
use SnowIO\Magento2DataModel\Command\DeleteAttributeCommand;
use SnowIO\Magento2DataModel\Command\SaveAttributeCommand;

class AttributeCommandEventMapperTest extends CommandEventMapperTest
{
    public function testSaveCommandMapper()
    {
        $eventJson = [
            'new' => [
                'code' => 'diameter',
                'type' => 'pim_catalog_identifier',
                'localizable' => false,
                'scopable' => false,
                'sort_order' => 3,
                'labels' => [
                    'en_GB' => 'Diameter',
                    'fr_FR' => 'Diamètre',
                ],
                'group' => 'default',
                '@timestamp' => 1510313694,
            ],
        ];

        $expected = SaveAttributeCommand::of(AttributeData::of('diameter', 'text',
            'Diameter'))->withTimestamp(1510313694);

        $mapper = AttributeMessageMapper::create($this->getMagentoConfiguration());
        $actual = $mapper->getSaveCommands($eventJson);
        self::assertEquals($expected->toJson(), iterator_to_array($actual)[0]->toJson());
    }

    public function testDeleteCommandMapper()
    {
        $eventJson = [
            'code' => 'diameter',
            'type' => 'pim_catalog_identifier',
            'localizable' => false,
            'scopable' => false,
            'sort_order' => 3,
            'labels' => [
                'en_GB' => 'Diameter',
                'fr_FR' => 'Diamètre',
            ],
            'group' => 'default',
            '@timestamp' => 1510313694,
        ];

        $expected = DeleteAttributeCommand::of('diameter')->withTimestamp(1510313694);

        $mapper = AttributeMessageMapper::create($this->getMagentoConfiguration());
        $actual = $mapper->getDeleteCommands($eventJson);
        self::assertEquals($expected->toJson(), iterator_to_array($actual)[0]->toJson());

    }
}
