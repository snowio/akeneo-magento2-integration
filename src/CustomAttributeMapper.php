<?php
declare(strict_types=1);
namespace SnowIO\AkeneoMagento2;

use SnowIO\AkeneoDataModel\AttributeValue;
use SnowIO\AkeneoDataModel\AttributeValueSet;
use SnowIO\AkeneoDataModel\PriceCollection;
use SnowIO\Magento2DataModel\CustomAttribute;
use SnowIO\Magento2DataModel\CustomAttributeSet;

final class CustomAttributeMapper
{
    public static function create(): self
    {
        return new self;
    }

    public function __invoke(AttributeValueSet $akeneoAttributeValues): CustomAttributeSet
    {
        foreach ($this->inputFilters as $inputFilter) {
            $akeneoAttributeValues = $akeneoAttributeValues->filter($inputFilter);
        }

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

            $attributeCode = ($this->attributeCodeMapper)($attributeValue->getAttributeCode());

            $customAttributes[] = CustomAttribute::of($attributeCode, $value);
        }

        foreach ($this->outputFilters as $outputFilter) {
            $customAttributes = \array_filter($customAttributes, $outputFilter);
        }

        return CustomAttributeSet::of($customAttributes);
    }

    public function withCurrency(string $currency): self
    {
        $result = clone $this;
        $result->currency = $currency;
        return $result;
    }

    public function withInputFilter(callable $predicate): self
    {
        $result = clone $this;
        $result->inputFilters[] = $predicate;
        return $result;
    }

    public function withOutputFilter(callable $predicate): self
    {
        $result = clone $this;
        $result->outputFilters[] = $predicate;
        return $result;
    }

    public function withAttributeCodeMapper(callable $attributeCodeMapper): self
    {
        $result = clone $this;
        $result->attributeCodeMapper = $attributeCodeMapper;
        return $result;
    }

    private $inputFilters = [];
    private $outputFilters = [];
    private $currency;
    private $attributeCodeMapper;

    private function __construct()
    {
        $this->attributeCodeMapper = function (string $attributeCode) {
            return $attributeCode;
        };
    }
}
