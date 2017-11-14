<?php
declare(strict_types=1);
namespace SnowIO\AkeneoMagento2\EventMapper;

use SnowIO\AkeneoDataModel\Event\ProductSavedEvent;
use SnowIO\Magento2DataModel\Command\SaveProductCategoryAssociationCommand;

final class ProductCategoryAssociationEventCommandMapper
{

    public static function create(MagentoConfiguration $configuration)
    {
        return new self($configuration);
    }

    public function getSaveCommands(array $eventJson)
    {
        $event = $event = ProductSavedEvent::fromJson($eventJson);
        $mapper = $this->configuration->getProductCategoryAssociationMapper();
        $magentoProductCategory = $mapper($event->getCurrentProductData());
        if ($magentoProductCategory != null){
            yield SaveProductCategoryAssociationCommand::of($magentoProductCategory)
                ->withTimestamp($event->getTimestamp());
        }

    }

    private $configuration;

    private function __construct(MagentoConfiguration $configuration)
    {
        $this->configuration = $configuration;
    }
}