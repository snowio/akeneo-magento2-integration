<?php
declare(strict_types=1);
namespace SnowIO\AkeneoMagento2;

use SnowIO\AkeneoDataModel\ProductData;
use SnowIO\Magento2DataModel\ProductCategoryAssociation;

final class ProductCategoryAssociationMapper
{

    public static function create(): self
    {
        return new self;
    }


    public function __invoke(ProductData $productData): ProductCategoryAssociation
    {
        $categories = $productData
            ->getProperties()
            ->getCategories()
            ->getCategoryCodes();
        $sku = $productData->getSku();

        return ProductCategoryAssociation::of($sku, $categories);

    }

    private function __construct()
    {

    }
}