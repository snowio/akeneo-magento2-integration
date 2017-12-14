<?php
declare(strict_types=1);
namespace SnowIO\AkeneoMagento2\MessageMapper;

use Joshdifabio\Transform\Filter;
use Joshdifabio\Transform\Kv;
use Joshdifabio\Transform\MapElements;
use Joshdifabio\Transform\Transform;
use SnowIO\AkeneoDataModel\Event\CategoryDeletedEvent;
use SnowIO\AkeneoDataModel\Event\CategorySavedEvent;
use SnowIO\AkeneoDataModel\Event\EntityStateEvent;
use SnowIO\Magento2DataModel\CategoryData;
use SnowIO\Magento2DataModel\Command\Command;
use SnowIO\Magento2DataModel\Command\DeleteCategoryCommand;
use SnowIO\Magento2DataModel\Command\SaveCategoryCommand;
use SnowIO\Magento2DataModel\Transform\CreateDeleteCategoryCommands;
use SnowIO\Magento2DataModel\Transform\CreateMoveCategoryCommands;
use SnowIO\Magento2DataModel\Transform\CreateSaveCategoryCommands;

final class CategoryMessageMapper extends MessageMapperWithDeleteSupport
{
    public static function withCategoryTransform(Transform $transform): self
    {
        return new self($transform);
    }

    public function transformAkeneoSavedEventToMagentoSaveCommands($categorySavedEvent): \Iterator
    {
        $categorySavedEvent = $this->resolveEntitySavedEvent($categorySavedEvent);

        $currentData = $this->dataTransform
            ->applyTo([$categorySavedEvent->getCurrentEntityData()]);
        $previousData = Filter::notEqualTo(null)
            ->then($this->dataTransform)
            ->applyTo([$categorySavedEvent->getPreviousEntityData()]);

        return CreateSaveCategoryCommands::fromIterables()
            ->then(MapElements::via(function (Command $command) use ($categorySavedEvent) {
                return $command->withTimestamp($categorySavedEvent->getTimestamp());
            }))
            ->applyTo([Kv::of('current', $currentData), Kv::of('previous', $previousData)]);
    }

    public function transformAkeneoSavedEventToMagentoMoveCommands($categorySavedEvent): \Iterator
    {
        $categorySavedEvent = $this->resolveEntitySavedEvent($categorySavedEvent);

        $currentData = $this->dataTransform
            ->applyTo([$categorySavedEvent->getCurrentEntityData()]);
        $previousData = Filter::notEqualTo(null)
            ->then($this->dataTransform)
            ->applyTo([$categorySavedEvent->getPreviousEntityData()]);

        return CreateMoveCategoryCommands::fromIterables()
            ->then(MapElements::via(function (Command $command) use ($categorySavedEvent) {
                return $command->withTimestamp($categorySavedEvent->getTimestamp());
            }))
            ->applyTo([Kv::of('current', $currentData), Kv::of('previous', $previousData)]);
    }

    public function transformAkeneoDeletedEventToMagentoDeleteCommands($categoryDeletedEvent): \Iterator
    {
        $categoryDeletedEvent = $this->resolveEntityDeletedEvent($categoryDeletedEvent);

        return $this->dataTransform
            ->then(CreateDeleteCategoryCommands::fromCategoryData())
            ->then(MapElements::via(function (Command $command) use ($categoryDeletedEvent) {
                return $command->withTimestamp($categoryDeletedEvent->getTimestamp());
            }))
            ->applyTo([$categoryDeletedEvent->getPreviousEntityData()]);
    }

    protected function resolveEntitySavedEvent($event): EntityStateEvent
    {
        if (\is_array($event)) {
            $event = CategorySavedEvent::fromJson($event);
        } elseif (!$event instanceof CategorySavedEvent) {
            throw new \InvalidArgumentException;
        }
        return $event;
    }

    protected function resolveEntityDeletedEvent($event): EntityStateEvent
    {
        if (\is_array($event)) {
            $event = CategoryDeletedEvent::fromJson($event);
        } elseif (!$event instanceof CategoryDeletedEvent) {
            throw new \InvalidArgumentException;
        }
        return $event;
    }

    /**
     * @param CategoryData $magentoEntityData
     */
    protected function getRepresentativeValueForDiff($magentoEntityData): string
    {
        return \json_encode($magentoEntityData->toJson());
    }

    /**
     * @param CategoryData $magentoEntityData
     */
    protected function getMagentoEntityIdentifier($magentoEntityData): string
    {
        return $magentoEntityData->getCode();
    }

    /**
     * @param CategoryData $magentoEntityData
     */
    protected function createSaveEntityCommand($magentoEntityData): Command
    {
        return SaveCategoryCommand::of($magentoEntityData);
    }

    protected function createDeleteEntityCommand(string $magentoEntityIdentifier): Command
    {
        return DeleteCategoryCommand::of($magentoEntityIdentifier);
    }
}
