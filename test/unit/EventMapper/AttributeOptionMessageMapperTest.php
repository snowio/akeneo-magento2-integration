<?php
declare(strict_types=1);
namespace SnowIO\AkeneoMagento2\Test\EventMapper;

use PHPUnit\Framework\TestCase;
use SnowIO\AkeneoMagento2\AttributeOptionMapper;
use SnowIO\AkeneoMagento2\MessageMapper\AttributeOptionMessageMapper;
use SnowIO\Magento2DataModel\AttributeOption;
use SnowIO\Magento2DataModel\Command\DeleteAttributeOptionCommand;
use SnowIO\Magento2DataModel\Command\SaveAttributeOptionCommand;

class AttributeOptionMessageMapperTest extends TestCase
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
        $actual = $this->getMessageMapper()->transformAkeneoSavedEventToMagentoSaveCommands($eventJson);
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
        $actual = $this->getMessageMapper()->transformAkeneoDeletedEventToMagentoDeleteCommands($eventJson);
        self::assertEquals($expected->toJson(), iterator_to_array($actual)[0]->toJson());
    }

    private function getMessageMapper(): AttributeOptionMessageMapper
    {
        $attributeOptionTransform = AttributeOptionMapper::withDefaultLocale('en_GB')->getTransform();
        return AttributeOptionMessageMapper::withAttributeOptionTransform($attributeOptionTransform);
    }
}
