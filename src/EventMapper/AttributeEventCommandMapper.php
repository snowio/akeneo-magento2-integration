<?php
declare(strict_types=1);
namespace SnowIO\AkeneoMagento2\EventMapper;

use SnowIO\AkeneoDataModel\Event\AttributeDeletedEvent;
use SnowIO\AkeneoDataModel\Event\AttributeSavedEvent;
use SnowIO\Magento2DataModel\AttributeData;
use SnowIO\Magento2DataModel\Command\DeleteAttributeCommand;
use SnowIO\Magento2DataModel\Command\SaveAttributeCommand;

final class AttributeEventCommandMapper
{
    public static function create(MagentoConfiguration $configuration): self
    {
        return new self($configuration);
    }

    public function getSaveCommands(array $eventJson): \Iterator {
        $event = AttributeSavedEvent::fromJson($eventJson);
        $mapper = $this->configuration->getAttributeMapper();
        /** @var AttributeData $magentoAttributeData */
        $magentoAttributeData = $mapper($event->getCurrentAttributeData());
        if ($magentoAttributeData !== null) {
            yield SaveAttributeCommand::of($magentoAttributeData)->withTimestamp($event->getTimestamp());
        }
    }

    public function getDeleteCommands(array $eventJson): \Iterator {
        $event = AttributeDeletedEvent::fromJson($eventJson);
        $mapper = $this->configuration->getAttributeMapper();
        /** @var AttributeData $magentoAttributeData */
        $magentoAttributeData = $mapper($event->getPreviousAttributeData());
        if ($magentoAttributeData !== null) {
            yield DeleteAttributeCommand::of($magentoAttributeData->getCode())->withTimestamp($event->getTimestamp());
        }
    }

    private $configuration;

    private function __construct(MagentoConfiguration $configuration)
    {
        $this->configuration = $configuration;
    }
}
