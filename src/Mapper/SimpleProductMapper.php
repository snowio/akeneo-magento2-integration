<?php
namespace SnowIO\AkeneoMagento2Integration\Mapper;

use SnowIO\AkeneoDataModel\ProductData as AkeneoProduct;
use SnowIO\Magento2DataModel\ProductData as Magento2Product;

class SimpleProductMapper
{
    public static function create(): self
    {
        $simpleProductMapper = new self;
        $simpleProductMapper->customAttributeMapper = CustomAttributeMapper::create();
        return $simpleProductMapper;
    }

    public function map(AkeneoProduct $akeneoProduct): Magento2Product
    {
        $magento2Product = Magento2Product::of($akeneoProduct->getSku());
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

    /** @var CustomAttributeMapper */
    private $customAttributeMapper;

    private function __construct()
    {

    }
}