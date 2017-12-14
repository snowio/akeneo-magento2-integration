<?php
declare(strict_types=1);
namespace SnowIO\AkeneoMagento2\MessageMapper;

use Joshdifabio\Transform\Pipeline;
use Joshdifabio\Transform\Diff;
use Joshdifabio\Transform\Filter;
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
            ->applyTo([[$entitySavedEvent->getCurrentEntityData()], [$entitySavedEvent->getPreviousEntityData()]]);
    }

    public function transformAkeneoDataToMagentoSaveCommands(): Transform
    {
        return Pipeline::of(
            Filter::notEqualTo([null]),
            MapElements::via([$this->dataTransform, 'applyTo']),
            Diff::withRepresentativeValue(function ($magentoEntityData) {
                return $this->getRepresentativeValueForDiff($magentoEntityData);
            }),
            MapElements::via(function ($magentoEntityData) {
                return $this->createSaveEntityCommand($magentoEntityData);
            })
        );
    }

    /** @var Transform */
    protected $dataTransform;

    protected function __construct(Transform $dataTransform)
    {
        $this->dataTransform = $dataTransform;
    }

    abstract protected function resolveEntitySavedEvent($event): EntityStateEvent;

    abstract protected function getRepresentativeValueForDiff($magentoEntityData): string;

    abstract protected function getMagentoEntityIdentifier($magentoEntityData): string;

    abstract protected function createSaveEntityCommand($magentoEntityData): Command;
}
