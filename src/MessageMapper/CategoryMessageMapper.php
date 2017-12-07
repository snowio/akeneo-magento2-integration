<?php
declare(strict_types=1);
namespace SnowIO\AkeneoMagento2\MessageMapper;

use Joshdifabio\Transform\Transform;
use SnowIO\AkeneoDataModel\Event\CategoryDeletedEvent;
use SnowIO\AkeneoDataModel\Event\CategorySavedEvent;
use SnowIO\AkeneoDataModel\Event\EntityStateEvent;
use SnowIO\Magento2DataModel\CategoryData;
use SnowIO\Magento2DataModel\Command\Command;
use SnowIO\Magento2DataModel\Command\DeleteCategoryCommand;
use SnowIO\Magento2DataModel\Command\SaveCategoryCommand;

final class CategoryMessageMapper extends MessageMapperWithDeleteSupport
{
    public static function withCategoryTransform(Transform $transform): self
    {
        return new self($transform);
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
