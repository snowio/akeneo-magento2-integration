<?php
declare(strict_types=1);
namespace SnowIO\AkeneoMagento2;

use Joshdifabio\Transform\MapElements;
use Joshdifabio\Transform\MapValues;
use Joshdifabio\Transform\Transform;
use Joshdifabio\Transform\WithKeys;
use SnowIO\AkeneoDataModel\CategoryData as AkeneoCategoryData;
use SnowIO\Magento2DataModel\CategoryData as Magento2CategoryData;

final class CategoryMapper
{
    public static function create(string $defaultLocale): self
    {
        return new self($defaultLocale);
    }

    public function getTransform(): Transform
    {
        return MapElements::via($this);
    }

    public function getKvTransform(): Transform
    {
        return WithKeys::ofInputElement()->then(MapValues::via($this));
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
