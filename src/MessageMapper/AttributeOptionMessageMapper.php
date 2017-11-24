<?php
declare(strict_types=1);
namespace SnowIO\AkeneoMagento2\MessageMapper;

use Joshdifabio\Transform\Transform;
use SnowIO\AkeneoDataModel\Event\AttributeOptionDeletedEvent;
use SnowIO\AkeneoDataModel\Event\AttributeOptionSavedEvent;
use SnowIO\AkeneoDataModel\Event\EntityStateEvent;
use SnowIO\Magento2DataModel\AttributeOption;
use SnowIO\Magento2DataModel\Command\Command;
use SnowIO\Magento2DataModel\Command\DeleteAttributeOptionCommand;
use SnowIO\Magento2DataModel\Command\SaveAttributeOptionCommand;

final class AttributeOptionMessageMapper extends MessageMapper
{
    public static function withAttributeOptionTransform(Transform $transform): self
    {
        return new self($transform);
    }

    protected function resolveEntitySavedEvent($event): EntityStateEvent
    {
        if (\is_array($event)) {
            $event = AttributeOptionSavedEvent::fromJson($event);
        } elseif (!$event instanceof AttributeOptionSavedEvent) {
            throw new \InvalidArgumentException;
        }
        return $event;
    }

    protected function resolveEntityDeletedEvent($event): EntityStateEvent
    {
        if (\is_array($event)) {
            $event = AttributeOptionDeletedEvent::fromJson($event);
        } elseif (!$event instanceof AttributeOptionDeletedEvent) {
            throw new \InvalidArgumentException;
        }
        return $event;
    }

    /**
     * @param AttributeOption $magentoEntityData
     */
    protected function getRepresentativeValueForDiff($magentoEntityData): string
    {
        return \json_encode($magentoEntityData->toJson());
    }

    /**
     * @param AttributeOption $magentoEntityData
     */
    protected function getMagentoEntityIdentifier($magentoEntityData): string
    {
        return "{$magentoEntityData->getAttributeCode()} {$magentoEntityData->getValue()}";
    }

    /**
     * @param AttributeOption $magentoEntityData
     */
    protected function createSaveEntityCommand($magentoEntityData): Command
    {
        return SaveAttributeOptionCommand::of($magentoEntityData);
    }

    protected function createDeleteEntityCommand(string $magentoEntityIdentifier): Command
    {
        list($attributeCode, $value) = \explode(' ', $magentoEntityIdentifier, $limit = 2);
        return DeleteAttributeOptionCommand::of($attributeCode, $value);
    }
}
