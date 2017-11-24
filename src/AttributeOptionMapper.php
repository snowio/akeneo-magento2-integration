<?php
declare(strict_types=1);
namespace SnowIO\AkeneoMagento2;

use Joshdifabio\Transform\MapElements;
use Joshdifabio\Transform\MapValues;
use Joshdifabio\Transform\Transform;
use Joshdifabio\Transform\WithKeys;
use SnowIO\Magento2DataModel\AttributeOption as Magento2AttributeOption;
use SnowIO\AkeneoDataModel\AttributeOption as AkeneoAttributeOption;

final class AttributeOptionMapper
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

    public function __invoke(AkeneoAttributeOption $attributeOption): Magento2AttributeOption
    {
        return Magento2AttributeOption::of(
            $attributeOption->getAttributeCode(),
            $attributeOption->getOptionCode(),
            $attributeOption->getLabel($this->defaultLocale)
        );
    }

    private $defaultLocale;

    private function __construct(string $defaultLocale)
    {
        $this->defaultLocale = $defaultLocale;
    }
}
