<?php
declare(strict_types=1);
namespace SnowIO\AkeneoMagento2\EventMapper;

use SnowIO\AkeneoDataModel\Event\CategoryDeletedEvent;
use SnowIO\AkeneoDataModel\Event\CategorySavedEvent;
use SnowIO\Magento2DataModel\CategoryData;
use SnowIO\Magento2DataModel\Command\DeleteCategoryCommand;
use SnowIO\Magento2DataModel\Command\SaveCategoryCommand;

final class CategoryEventCommandMapper
{

    public static function create(MagentoConfiguration $configuration): self
    {
        return new self($configuration);
    }

    public function getSaveCommands(array $eventJson): \Iterator {
        $event = CategorySavedEvent::fromJson($eventJson);
        $mapper = $this->configuration->getCategoryMapper();
        /** @var CategoryData $magentoCategoryData */
        $magentoCategoryData = $mapper($event->getCurrentCategoryData());
        if ($magentoCategoryData !== null) {
            yield SaveCategoryCommand::of($magentoCategoryData)->withTimestamp($event->getTimestamp());
        }
    }

    public function getDeleteCommands(array $eventJson): \Iterator {
        $event = CategoryDeletedEvent::fromJson($eventJson);
        $mapper = $this->configuration->getCategoryMapper();
        /** @var CategoryData $magentoCategoryData */
        $magentoCategoryData = $mapper($event->getPreviousCategoryData());
        if ($magentoCategoryData !== null) {
            yield DeleteCategoryCommand::of($magentoCategoryData->getCode())->withTimestamp($event->getTimestamp());
        }
    }

    private $configuration;

    private function __construct(MagentoConfiguration $configuration)
    {
        $this->configuration = $configuration;
    }
}