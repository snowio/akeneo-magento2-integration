<?php
declare(strict_types=1);
namespace SnowIO\AkeneoMagento2\EventMapper;

use SnowIO\AkeneoDataModel\Event\AttributeOptionDeletedEvent;
use SnowIO\AkeneoDataModel\Event\AttributeOptionSavedEvent;
use SnowIO\Magento2DataModel\AttributeOption;
use SnowIO\Magento2DataModel\Command\DeleteAttributeOptionCommand;
use SnowIO\Magento2DataModel\Command\SaveAttributeOptionCommand;

final class AttributeOptionEventCommandMapper
{

    public static function create(MagentoConfiguration $configuration)
    {
        return new self($configuration);
    }

    public function getSaveCommands(array $eventJson): \Iterator {
        $event = AttributeOptionSavedEvent::fromJson($eventJson);
        $mapper = $this->magentoConfiguration->getAttributeOptionMapper();
        /** @var AttributeOption $magentoAttributeOption */
        $magentoAttributeOption = $mapper($event->getCurrentAttributeOptionData());
        if ($magentoAttributeOption !== null) {
            yield SaveAttributeOptionCommand::of($magentoAttributeOption)->withTimestamp($event->getTimestamp());
        }
    }

    public function getDeleteCommands(array $eventJson): \Iterator {
        $event = AttributeOptionDeletedEvent::fromJson($eventJson);
        $mapper = $this->magentoConfiguration->getAttributeOptionMapper();
        /** @var AttributeOption $m2AttributeOption */
        $m2AttributeOption = $mapper($event->getPreviousAttributeOptionData());
        if ($m2AttributeOption !== null) {
            yield DeleteAttributeOptionCommand::of($m2AttributeOption->getAttributeCode(), $m2AttributeOption->getValue())
                ->withTimestamp($event->getTimestamp());
        }
    }

    private $magentoConfiguration;

    private function __construct(MagentoConfiguration $configuration)
    {
        $this->magentoConfiguration = $configuration;
    }
}