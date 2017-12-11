<?php
declare(strict_types=1);
namespace SnowIO\AkeneoMagento2\Test\EventMapper;

use Joshdifabio\Transform\FlatMapElements;
use Joshdifabio\Transform\Transform;
use PHPUnit\Framework\TestCase;
use SnowIO\AkeneoMagento2\CategoryMapper;
use SnowIO\AkeneoMagento2\MessageMapper\CategoryMessageMapper;
use SnowIO\AkeneoMagento2\Test\CommandSet;
use SnowIO\Magento2DataModel\CategoryData;
use SnowIO\Magento2DataModel\Command\DeleteCategoryCommand;
use SnowIO\Magento2DataModel\Command\MoveCategoryCommand;
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

    /**
     * @dataProvider getMoveCommandTestData
     */
    public function testMoveCommandMapper(Transform $dataTransform, $event, CommandSet $expectedCommands)
    {
        $messageMapper = CategoryMessageMapper::withCategoryTransform($dataTransform);
        $actualCommands = $messageMapper->transformAkeneoSavedEventToMagentoMoveCommands($event);
        self::assertTrue(CommandSet::of(...$actualCommands)->equals($expectedCommands));
    }

    public function getMoveCommandTestData()
    {
        yield [
            CategoryMapper::withDefaultLocale('en_GB')->getTransform(),
            [
                'new' => [
                    'code' => 'mens_trousers',
                    '@timestamp' => 1510313694,
                    'labels' => [
                        'en_GB' => 'Men\'s Trousers',
                    ],
                    'path' => ['clothes', 'mens_wear', 'mens_trousers']
                ],
            ],
            CommandSet::create()
        ];

        yield [
            CategoryMapper::withDefaultLocale('en_GB')->getTransform(),
            [
                'old' => [
                    'code' => 'mens_trousers',
                    '@timestamp' => 1510313693,
                    'labels' => [
                        'en_GB' => 'Men\'s Trousers',
                    ],
                    'path' => ['clothes', 'mens_wear', 'mens_trousers']
                ],
                'new' => [
                    'code' => 'mens_trousers',
                    '@timestamp' => 1510313694,
                    'labels' => [
                        'en_GB' => 'Men\'s Trousers',
                    ],
                    'path' => ['clothes', 'mens_wear', 'mens_trousers']
                ],
            ],
            CommandSet::create()
        ];

        yield [
            CategoryMapper::withDefaultLocale('en_GB')->getTransform(),
            [
                'old' => [
                    'code' => 'mens_trousers',
                    '@timestamp' => 1510313693,
                    'labels' => [
                        'en_GB' => 'Men\'s Trousers',
                    ],
                    'path' => ['clothes', 'men_wear', 'mens_trousers']
                ],
                'new' => [
                    'code' => 'mens_trousers',
                    '@timestamp' => 1510313694,
                    'labels' => [
                        'en_GB' => 'Men\'s Trousers',
                    ],
                    'path' => ['clothes', 'mens_wear', 'mens_trousers']
                ],
            ],
            CommandSet::of(
                MoveCategoryCommand::of('mens_trousers', 'mens_wear')->withTimestamp(1510313694)
            )
        ];

        yield [
            CategoryMapper::withDefaultLocale('en_GB')->getTransform()
                ->then(FlatMapElements::via(function (CategoryData $magentoCategoryData) {
                    yield $magentoCategoryData;
                    yield CategoryData::of($magentoCategoryData->getCode() . '_2', $magentoCategoryData->getName())
                        ->withParentCode($magentoCategoryData->getParentCode());
                })),
            [
                'old' => [
                    'code' => 'mens_trousers',
                    '@timestamp' => 1510313693,
                    'labels' => [
                        'en_GB' => 'Men\'s Trousers',
                    ],
                    'path' => ['clothes', 'men_wear', 'mens_trousers']
                ],
                'new' => [
                    'code' => 'mens_trousers',
                    '@timestamp' => 1510313694,
                    'labels' => [
                        'en_GB' => 'Men\'s Trousers',
                    ],
                    'path' => ['clothes', 'mens_wear', 'mens_trousers']
                ],
            ],
            CommandSet::of(
                MoveCategoryCommand::of('mens_trousers', 'mens_wear')->withTimestamp(1510313694),
                MoveCategoryCommand::of('mens_trousers_2', 'mens_wear')->withTimestamp(1510313694)
            )
        ];
    }

    private function getMessageMapper(): CategoryMessageMapper
    {
        $categoryTransform = CategoryMapper::withDefaultLocale('en_GB')->getTransform();
        return CategoryMessageMapper::withCategoryTransform($categoryTransform);
    }
}
