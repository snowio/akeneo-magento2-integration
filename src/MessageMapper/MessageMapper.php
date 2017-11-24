<?php
declare(strict_types=1);
namespace SnowIO\AkeneoMagento2\MessageMapper;

use Joshdifabio\Transform\Pipeline;
use Joshdifabio\Transform\Diff;
use Joshdifabio\Transform\Filter;
use Joshdifabio\Transform\FlatMapElements;
use Joshdifabio\Transform\MapElements;
use Joshdifabio\Transform\Transform;
use SnowIO\AkeneoDataModel\Event\EntityStateEvent;
use SnowIO\Magento2DataModel\Command\Command;

abstract class MessageMapper
{
    public function transformAkeneoSavedEventToMagentoSaveCommands($entitySavedEvent): \Iterator
    {
        $entitySavedEvent = $this->resolveEntitySavedEvent($entitySavedEvent);

        return $this->transformAkeneoDataToMagentoSaveCommands()
            ->then(MapElements::via(function (Command $command) use ($entitySavedEvent) {
                return $command->withTimestamp($entitySavedEvent->getTimestamp());
            }))
            ->applyTo([[$entitySavedEvent->getPreviousEntityData()], [$entitySavedEvent->getCurrentEntityData()]]);
    }

    public function transformAkeneoSavedEventToMagentoDeleteCommands($entitySavedEvent): \Iterator
    {
        $entitySavedEvent = $this->resolveEntitySavedEvent($entitySavedEvent);

        return $this->transformAkeneoDataToMagentoDeleteCommands()
            ->then(MapElements::via(function (Command $command) use ($entitySavedEvent) {
                return $command->withTimestamp($entitySavedEvent->getTimestamp());
            }))
            ->applyTo([[$entitySavedEvent->getPreviousEntityData()], [$entitySavedEvent->getCurrentEntityData()]]);
    }

    public function transformAkeneoDeletedEventToMagentoDeleteCommands($entityDeletedEvent): \Iterator
    {
        $entityDeletedEvent = $this->resolveEntitySavedEvent($entityDeletedEvent);

        return $this->transformAkeneoDataToMagentoDeleteCommands()
            ->then(MapElements::via(function (Command $command) use ($entityDeletedEvent) {
                return $command->withTimestamp($entityDeletedEvent->getTimestamp());
            }))
            ->applyTo([[$entityDeletedEvent->getPreviousEntityData()]]);
    }

    public function transformAkeneoDataToMagentoSaveCommands(): Transform
    {
        return Pipeline::of(
            Filter::notEqualTo([null]),
            FlatMapElements::viaTransform($this->dataTransform),
            Diff::withRepresentativeValue(function ($magentoEntityData) {
                return $this->getRepresentativeValueForDiff($magentoEntityData);
            }),
            MapElements::via(function ($magentoEntityData) {
                return $this->createSaveEntityCommand($magentoEntityData);
            })
        );
    }

    public function transformAkeneoDataToMagentoDeleteCommands(): Transform
    {
        return Pipeline::of(
            Filter::notEqualTo([null]),
            FlatMapElements::viaTransform(
                $this->dataTransform->then(MapElements::via(function ($magentoEntityData) {
                    return $this->getMagentoEntityIdentifier($magentoEntityData);
                }))
            ),
            Diff::create(),
            MapElements::via(function ($magentoEntityIdentifier) {
                return $this->createDeleteEntityCommand($magentoEntityIdentifier);
            })
        );
    }

    protected function __construct(Transform $dataTransform)
    {
        $this->dataTransform = $dataTransform;
    }

    abstract protected function resolveEntitySavedEvent($event): EntityStateEvent;

    abstract protected function resolveEntityDeletedEvent($event): EntityStateEvent;

    abstract protected function getRepresentativeValueForDiff($magentoEntityData): string;

    abstract protected function getMagentoEntityIdentifier($magentoEntityData): string;

    abstract protected function createSaveEntityCommand($magentoEntityData): Command;

    abstract protected function createDeleteEntityCommand(string $magentoEntityIdentifier): Command;

    /** @var Transform */
    private $dataTransform;
}
