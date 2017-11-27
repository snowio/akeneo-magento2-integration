<?php
declare(strict_types=1);
namespace SnowIO\AkeneoMagento2\Test\EventMapper;

use PHPUnit\Framework\TestCase;
use SnowIO\AkeneoMagento2\CategoryMapper;
use SnowIO\AkeneoMagento2\MessageMapper\CategoryMessageMapper;
use SnowIO\AkeneoMagento2\MessageMapper\MessageMapper;
use SnowIO\Magento2DataModel\CategoryData;
use SnowIO\Magento2DataModel\Command\DeleteCategoryCommand;
use SnowIO\Magento2DataModel\Command\SaveCategoryCommand;

class CategoryMessageMapperTest extends TestCase
{
    public function testSaveCommandMapper()
    {
        $eventJson = [
            'new' => [
                'code' => 'mens_trousers',
                '@timestamp' => 1510313694,
                'labels' => [
                    'en_GB' => 'Men\'s Trousers',
                ],
                'parent' => 'mens_wear',
                'path' => ['clothes', 'mens_wear', 'mens_trousers']
            ],
        ];

        $expected = SaveCategoryCommand::of(CategoryData::of('mens_trousers', 'Men\'s Trousers')->withParentCode('mens_wear'))
            ->withTimestamp(1510313694);
        $actual = $this->getMessageMapper()->transformAkeneoSavedEventToMagentoSaveCommands($eventJson);
        self::assertEquals($expected->toJson(), iterator_to_array($actual)[0]->toJson());
    }

    public function testDeleteCommandMapper()
    {
        $eventJson = [
            '@timestamp' => 1510313694,
            'categoryCode' => 'mens_trousers',
            'path' => ['clothes', 'mens_wear', 'mens_trousers'],
            'labels' => [
                'en_GB' => 'Men\'s Trousers',
            ],
        ];

        $expected = DeleteCategoryCommand::of('mens_trousers')->withTimestamp(1510313694);
        $actual = $this->getMessageMapper()->transformAkeneoDeletedEventToMagentoDeleteCommands($eventJson);
        self::assertEquals($expected->toJson(), iterator_to_array($actual)[0]->toJson());
    }

    private function getMessageMapper(): MessageMapper
    {
        $categoryTransform = CategoryMapper::withDefaultLocale('en_GB')->getTransform();
        return CategoryMessageMapper::withCategoryTransform($categoryTransform);
    }
}
