<?php
namespace SnowIO\AkeneoMagento2Integration\Mapper;

use SnowIO\AkeneoDataModel\ProductData as AkeneoProduct;
use SnowIO\Magento2DataModel\ProductData as Magento2Product;

class SimpleProductMapper
{
    public static function create(): self
    {
    }

    public function map(AkeneoProduct $akeneoProduct): Magento2Product
    {

    }

    public function withCustomAttributeMapper(CustomAttributeMapper $customAttributeMapper): self
    {
    }
}