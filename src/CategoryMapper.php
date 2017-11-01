<?php
declare(strict_types=1);
namespace SnowIO\AkeneoMagento2;

use SnowIO\AkeneoDataModel\CategoryData as AkeneoCategoryData;
use SnowIO\Magento2DataModel\CategoryData as Magento2CategoryData;

final class CategoryMapper
{
    public static function create(string $defaultLocale): self
    {
        return new self($defaultLocale);
    }

    public function __invoke(AkeneoCategoryData $categoryData): Magento2CategoryData
    {
        $code = $categoryData->getCode();
        $name = $categoryData->getLabel($this->defaultLocale);
        $parent = $categoryData->getParent();
        $category = Magento2CategoryData::of($code, $name);
        if ($parent !== null) {
            $category = $category->withParentCode($parent);
        }
        return $category;
    }

    private $defaultLocale;

    private function __construct(string $defaultLocale)
    {
        $this->defaultLocale = $defaultLocale;
    }
}
