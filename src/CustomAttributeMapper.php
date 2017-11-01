<?php
declare(strict_types=1);
namespace SnowIO\AkeneoMagento2;

use SnowIO\AkeneoDataModel\AttributeValue;
use SnowIO\AkeneoDataModel\AttributeValueSet;
use SnowIO\AkeneoDataModel\PriceCollection;
use SnowIO\Magento2DataModel\CustomAttribute;
use SnowIO\Magento2DataModel\CustomAttributeSet;

final class CustomAttributeMapper extends Mapper
{
    public static function create(): self
    {
        return new self;
    }

    public function __invoke(AttributeValueSet $akeneoAttributeValues): CustomAttributeSet
    {
        $akeneoAttributeValues = $this->filterInputSet($akeneoAttributeValues);

        $customAttributes = [];
        /** @var AttributeValue $attributeValue */
        foreach ($akeneoAttributeValues as $attributeValue) {
            $value = $attributeValue->getValue();

            if ($value instanceof PriceCollection) {
                if (null === $this->currency) {
                    continue;
                }

                $value = $value->getAmount($this->currency);
            }

            $customAttributes[] = CustomAttribute::of($attributeValue->getAttributeCode(), $value);
        }

        return $this->filterOutputSet(CustomAttributeSet::of($customAttributes));
    }

    public function withCurrency(string $currency): self
    {
        $result = clone $this;
        $result->currency = $currency;
        return $result;
    }

    private $currency;

    private function __construct()
    {

    }
}
