<?php
declare(strict_types=1);
namespace SnowIO\AkeneoMagento2Integration\Mapper;

use SnowIO\AkeneoDataModel\ProductData as AkeneoProductData;
use SnowIO\Magento2DataModel\ProductData as Magento2ProductData;
use SnowIO\Magento2DataModel\ProductStatus;

final class SimpleProductMapper
{
    public static function create(): self
    {
        $simpleProductMapper = new self;
        $simpleProductMapper->customAttributeMapper = CustomAttributeMapper::create();
        $simpleProductMapper->attributeSetCodeMapper = function (string $family = null) {
            return $family;
        };
        return $simpleProductMapper;
    }

    public function map(AkeneoProductData $akeneoProduct): Magento2ProductData
    {
        $magento2Product = Magento2ProductData::of($akeneoProduct->getSku(), $akeneoProduct->getSku());
        $attributeSetCode = ($this->attributeSetCodeMapper)($akeneoProduct->getProperties()->getFamily());
        if ($attributeSetCode !== null) {
            $magento2Product = $magento2Product->withAttributeSetCode($attributeSetCode);
        }
        $status = $akeneoProduct->getProperties()->getEnabled() ? ProductStatus::ENABLED : ProductStatus::DISABLED;
        $magento2Product = $magento2Product->withStatus($status);
        $customAttributes = $this->customAttributeMapper->map($akeneoProduct->getAttributeValues());
        return $magento2Product->withCustomAttributes($customAttributes);
    }

    public function withCustomAttributeMapper(CustomAttributeMapper $customAttributeMapper): self
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

    }
}
