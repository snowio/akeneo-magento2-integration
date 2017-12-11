<?php
declare(strict_types=1);
namespace SnowIO\AkeneoMagento2\MessageMapper;

use Joshdifabio\Transform\CoGbkResult;
use Joshdifabio\Transform\CoGroupByKey;
use Joshdifabio\Transform\Filter;
use Joshdifabio\Transform\Kv;
use Joshdifabio\Transform\MapElements;
use Joshdifabio\Transform\MapValues;
use Joshdifabio\Transform\Pipeline;
use Joshdifabio\Transform\Transform;
use Joshdifabio\Transform\WithKeys;
use SnowIO\AkeneoDataModel\Event\CategoryDeletedEvent;
use SnowIO\AkeneoDataModel\Event\CategorySavedEvent;
use SnowIO\AkeneoDataModel\Event\EntityStateEvent;
use SnowIO\Magento2DataModel\CategoryData;
use SnowIO\Magento2DataModel\Command\Command;
use SnowIO\Magento2DataModel\Command\DeleteCategoryCommand;
use SnowIO\Magento2DataModel\Command\MoveCategoryCommand;
use SnowIO\Magento2DataModel\Command\SaveCategoryCommand;

final class CategoryMessageMapper extends MessageMapperWithDeleteSupport
{
    public static function withCategoryTransform(Transform $transform): self
    {
        return new self($transform);
    }

    public function transformAkeneoSavedEventToMagentoMoveCommands($categorySavedEvent): \Iterator
    {
        $categorySavedEvent = $this->resolveEntitySavedEvent($categorySavedEvent);

        $getDataAndApplyKeys = Pipeline::of(
            $this->dataTransform,
            WithKeys::of(function (CategoryData $magentoCategoryData) {
                return $magentoCategoryData->getCode();
            })
        );

        $previousParentCodes = Filter::notEqualTo(null)
            ->then($getDataAndApplyKeys)
            ->applyTo([$categorySavedEvent->getPreviousEntityData()]);

        $currentParentCodes = $getDataAndApplyKeys
            ->applyTo([$categorySavedEvent->getCurrentEntityData()]);

        $getMoveCategoryCommands = Pipeline::of(
            CoGroupByKey::create(),
            Filter::byValue(function (CoGbkResult $result) {
                /** @var CategoryData|null $currentCategoryData */
                $currentCategoryData = $result->getOptional('current');
                /** @var CategoryData|null $previousCategoryData */
                $previousCategoryData = $result->getOptional('previous');
                return isset($currentCategoryData, $previousCategoryData)
                    && $currentCategoryData->getParentCode() !== $previousCategoryData->getParentCode();
            }),
            MapValues::via(function (CoGbkResult $result) {
                return $result->getOnly('current')->getParentCode();
            }),
            MapElements::via(Kv::unpack(function (string $categoryCode, string $parentCategoryCode) use ($categorySavedEvent) {
                return MoveCategoryCommand::of($categoryCode, $parentCategoryCode)
                    ->withTimestamp($categorySavedEvent->getTimestamp());
            }))
        );

        return $getMoveCategoryCommands->applyTo([
            Kv::of('previous', $previousParentCodes),
            Kv::of('current', $currentParentCodes),
        ]);
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
