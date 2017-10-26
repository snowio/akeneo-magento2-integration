<?php
declare(strict_types=1);
namespace SnowIO\AkeneoMagento2Integration\Mapper;

use SnowIO\AkeneoDataModel\ProductData as AkeneoProductData;
use SnowIO\Magento2DataModel\ProductData as Magento2ProductData;

final class SimpleProductMapper
{
    public static function create(): self
    {
        $simpleProductMapper = new self;
        $simpleProductMapper->customAttributeMapper = CustomAttributeMapper::create();
        $simpleProductMapper->customNameMapper = function (AkeneoProductData $productData) {
            return $productData->getSku();
        };
        return $simpleProductMapper;
    }

    public function map(AkeneoProductData $akeneoProduct): Magento2ProductData
    {
        $name = ($this->customNameMapper)($akeneoProduct);
        $magento2Product = Magento2ProductData::of($akeneoProduct->getSku(), $name);
        $customAttributes = $this->customAttributeMapper->map($akeneoProduct->getAttributeValues());
        $magento2Product = $magento2Product->withCustomAttributes($customAttributes);
        return $magento2Product;
    }

    public function withCustomAttributeMapper(CustomAttributeMapper $customAttributeMapper): self
    {
        $result = clone $this;
        $result->customAttributeMapper = $customAttributeMapper;
        return $result;
    }

    public function withCustomNameMapper(callable $fn): self
    {
        $result = clone $this;
        $result->customAttributeMapper = $fn;
        return $result;
    }

    /** @var CustomAttributeMapper */
    private $customAttributeMapper;

    /** @var callable */
    private $customNameMapper;

    private function __construct()
    {

    }

}