<?php
declare(strict_types=1);
namespace SnowIO\AkeneoMagento2Integration;

use SnowIO\AkeneoDataModel\ProductData as AkeneoProductData;
use SnowIO\Magento2DataModel\ProductData as Magento2ProductData;
use SnowIO\Magento2DataModel\ProductStatus;

final class SimpleProductMapper
{
    public static function create(): self
    {
        return new self;
    }

    public function __invoke(AkeneoProductData $akeneoProduct): Magento2ProductData
    {
        $magento2Product = Magento2ProductData::of($akeneoProduct->getSku(), $akeneoProduct->getSku());
        $attributeSetCode = ($this->attributeSetCodeMapper)($akeneoProduct->getProperties()->getFamily());
        if ($attributeSetCode !== null) {
            $magento2Product = $magento2Product->withAttributeSetCode($attributeSetCode);
        }
        $status = $akeneoProduct->getProperties()->getEnabled() ? ProductStatus::ENABLED : ProductStatus::DISABLED;
        $magento2Product = $magento2Product->withStatus($status);
        $customAttributes = ($this->customAttributeMapper)($akeneoProduct->getAttributeValues());
        return $magento2Product->withCustomAttributes($customAttributes);
    }

    public function withCustomAttributeMapper(callable $customAttributeMapper): self
    {
        $result = clone $this;
        $result->customAttributeMapper = $customAttributeMapper;
        return $result;
    }

    public function withAttributeSetCodeMapper(callable $fn): self
    {
        $result = clone $this;
        $result->attributeSetCodeMapper = $fn;
        return $result;
    }

    /** @var CustomAttributeMapper */
    private $customAttributeMapper;

    /** @var callable */
    private $attributeSetCodeMapper;

    private function __construct()
    {
        $this->customAttributeMapper = CustomAttributeMapper::create();
        $this->attributeSetCodeMapper = function (string $family = null) {
            return $family;
        };
    }
}
