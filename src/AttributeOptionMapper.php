<?php
declare(strict_types=1);
namespace SnowIO\AkeneoMagento2;

use SnowIO\Magento2DataModel\AttributeOption as Magento2AttributeOption;
use SnowIO\AkeneoDataModel\AttributeOption as AkeneoAttributeOption;

final class AttributeOptionMapper extends Mapper
{
    public static function create(string $defaultLocale): self
    {
        return new self($defaultLocale);
    }

    public function __invoke(AkeneoAttributeOption $akeneoAttributeOption): ?Magento2AttributeOption
    {
        if ($this->inputIsIgnored($akeneoAttributeOption)) {
            return null;
        }
        $magentoAttributeOption = Magento2AttributeOption::of(
            $akeneoAttributeOption->getAttributeCode(),
            $akeneoAttributeOption->getOptionCode(),
            $akeneoAttributeOption->getLabel($this->defaultLocale)
        );
        return $this->filterOutput($magentoAttributeOption);
    }

    private $defaultLocale;

    private function __construct(string $defaultLocale)
    {
        $this->defaultLocale = $defaultLocale;
    }
}
