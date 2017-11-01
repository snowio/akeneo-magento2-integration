<?php
declare(strict_types=1);
namespace SnowIO\AkeneoMagento2Integration\Mapper;

use SnowIO\Magento2DataModel\AttributeOption as Magento2AttributeOption;
use SnowIO\AkeneoDataModel\AttributeOption as AkeneoAttributeOption;

final class AttributeOptionMapper
{
    public static function create(string $defaultLocale): self
    {
        return new self($defaultLocale);
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
