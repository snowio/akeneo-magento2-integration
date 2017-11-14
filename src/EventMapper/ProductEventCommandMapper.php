<?php
declare(strict_types=1);
namespace SnowIO\AkeneoMagento2\EventMapper;

use SnowIO\AkeneoDataModel\Event\ProductSavedEvent;
use SnowIO\Magento2DataModel\Command\DeleteProductCommand;
use SnowIO\Magento2DataModel\Command\SaveProductCommand;
use SnowIO\Magento2DataModel\ProductData;
use SnowIO\Magento2DataModel\ProductDataSet;

final class ProductEventCommandMapper
{

    public static function create(MagentoConfiguration $configuration): self
    {
        return new self($configuration);
    }

    public function getSaveCommands(array $eventJson): \Iterator {
        $event = ProductSavedEvent::fromJson($eventJson);
        $mapper = $this->configuration->getProductMapper();
        /** @var ProductDataSet $currentM2ProductsData */
        $currentM2ProductsData = $mapper($event->getCurrentProductData());
        /** @var ProductDataSet $previousM2ProductsData */
        $previousM2ProductsData = $mapper($event->getPreviousProductData());
        /** @var ProductDataSet $changedProducts */
        $changedProducts = $currentM2ProductsData->diff($previousM2ProductsData);
        foreach ($changedProducts as $changedProduct) {
            yield SaveProductCommand::of($changedProduct)->withTimestamp($event->getTimestamp());
        }
    }
    
    public function getDeleteCommands(array $eventJson): \Iterator {
        $event = ProductSavedEvent::fromJson($eventJson);
        $mapper = $this->configuration->getProductMapper();
        /** @var ProductDataSet $currentM2ProductsData */
        $currentM2ProductsData = $mapper($event->getCurrentProductData());
        /** @var ProductDataSet $previousM2ProductsData */
        $previousM2ProductsData = $mapper($event->getPreviousProductData());
        /** @var ProductDataSet $removedProducts */
        $removedProducts = $previousM2ProductsData
            ->diffByKey($currentM2ProductsData)
            ->filter(function (ProductData $magentoProductData) {
                return $magentoProductData->getStoreCode() === 'admin';
            });
        /** @var ProductData $removedProduct */
        foreach ($removedProducts as $removedProduct) {
            yield DeleteProductCommand::of($removedProduct->getSku())->withTimestamp($event->getTimestamp());
        }
    }

    private $configuration;

    private function __construct(MagentoConfiguration $configuration)
    {
        $this->configuration = $configuration;
    }
}