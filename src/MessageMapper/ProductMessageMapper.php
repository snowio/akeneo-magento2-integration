<?php
declare(strict_types=1);
namespace SnowIO\AkeneoMagento2\MessageMapper;

use Joshdifabio\Transform\Transform;
use SnowIO\AkeneoDataModel\Event\ProductDeletedEvent;
use SnowIO\AkeneoDataModel\Event\ProductSavedEvent;
use SnowIO\AkeneoDataModel\Event\EntityStateEvent;
use SnowIO\AkeneoMagento2\ProductMapper;
use SnowIO\Magento2DataModel\Command\Command;
use SnowIO\Magento2DataModel\ProductData;
use SnowIO\Magento2DataModel\Command\DeleteProductCommand;
use SnowIO\Magento2DataModel\Command\SaveProductCommand;

final class ProductMessageMapper extends MessageMapper
{
    public static function create(): self
    {
        return new self(ProductMapper::create()->getTransform());
    }

    public function withProductTransform(Transform $transform): self
    {
        return new self($transform);
    }

    protected function resolveEntitySavedEvent($event): EntityStateEvent
    {
        if (\is_array($event)) {
            $event = ProductSavedEvent::fromJson($event);
        } elseif (!$event instanceof ProductSavedEvent) {
            throw new \InvalidArgumentException;
        }
        return $event;
    }

    protected function resolveEntityDeletedEvent($event): EntityStateEvent
    {
        if (\is_array($event)) {
            $event = ProductDeletedEvent::fromJson($event);
        } elseif (!$event instanceof ProductDeletedEvent) {
            throw new \InvalidArgumentException;
        }
        return $event;
    }

    /**
     * @param ProductData $magentoEntityData
     */
    protected function getRepresentativeValueForDiff($magentoEntityData): string
    {
        return \json_encode($magentoEntityData->toJson());
    }

    /**
     * @param ProductData $magentoEntityData
     */
    protected function getMagentoEntityIdentifier($magentoEntityData): string
    {
        return $magentoEntityData->getSku();
    }

    /**
     * @param ProductData $magentoEntityData
     */
    protected function createSaveEntityCommand($magentoEntityData): Command
    {
        return SaveProductCommand::of($magentoEntityData);
    }

    protected function createDeleteEntityCommand(string $magentoEntityIdentifier): Command
    {
        return DeleteProductCommand::of($magentoEntityIdentifier);
    }
}
