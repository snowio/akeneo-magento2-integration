<?php
declare(strict_types = 1);
namespace SnowIO\AkeneoMagento2\Test\EventMapper;

use PHPUnit\Framework\TestCase;
use SnowIO\AkeneoMagento2\AttributeMapper;
use SnowIO\AkeneoMagento2\MessageMapper\AttributeMessageMapper;
use SnowIO\AkeneoMagento2\MessageMapper\MessageMapper;
use SnowIO\Magento2DataModel\AttributeData;
use SnowIO\Magento2DataModel\Command\DeleteAttributeCommand;
use SnowIO\Magento2DataModel\Command\SaveAttributeCommand;

class AttributeMessageMapperTest extends TestCase
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

        $expectedAttributeData = AttributeData::of('diameter', 'text', 'Diameter');
        $expected = SaveAttributeCommand::of($expectedAttributeData)->withTimestamp(1510313694);
        $actual = $this->getMessageMapper()->transformAkeneoSavedEventToMagentoSaveCommands($eventJson);
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
        $actual = $this->getMessageMapper()->transformAkeneoDeletedEventToMagentoDeleteCommands($eventJson);
        self::assertEquals($expected->toJson(), iterator_to_array($actual)[0]->toJson());
    }

    private function getMessageMapper(): MessageMapper
    {
        $attributeTransform = AttributeMapper::withDefaultLocale('en_GB')->getTransform();
        return AttributeMessageMapper::withAttributeTransform($attributeTransform);
    }
}
