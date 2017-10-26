<?php
namespace SnowIO\AkeneoMagento2Integration\Mapper;

use SnowIO\AkeneoDataModel\ProductData as AkeneoProductData;
use SnowIO\Magento2DataModel\ProductData as Magento2ProductData;

class SimpleProductMapper
{
    public static function create(): self
    {
        $simpleProductMapper = new self;
        $simpleProductMapper->customAttributeMapper = CustomAttributeMapper::create();
        return $simpleProductMapper;
    }

    public function map(AkeneoProductData $akeneoProduct): Magento2ProductData
    {
        $magento2Product = Magento2ProductData::of($akeneoProduct->getSku());
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
    }

    /** @var CustomAttributeMapper */
    private $customAttributeMapper;

    private function __construct()
    {

    }

}