<?php
declare(strict_types=1);
namespace SnowIO\AkeneoMagento2;

use SnowIO\AkeneoDataModel\AttributeData as AkeneoAttributeData;
use SnowIO\AkeneoDataModel\AttributeType;
use SnowIO\Magento2DataModel\AttributeData as Magento2AttributeData;
use SnowIO\Magento2DataModel\FrontendInput;

final class AttributeMapper extends Mapper
{
    public static function create(string $defaultLocale): self
    {
        $attributeMapper = new self;
        $attributeMapper->defaultLocale = $defaultLocale;
        return $attributeMapper;
    }

    public function __invoke(AkeneoAttributeData $akeneoAttributeData): ?Magento2AttributeData
    {
        if ($this->inputIsIgnored($akeneoAttributeData)) {
            return null;
        }
        $frontendInput = ($this->typeToFrontendInputMapper)($akeneoAttributeData->getType());
        $defaultFrontendLabel = $akeneoAttributeData->getLabel($this->defaultLocale);
        $magentoAttributeData = Magento2AttributeData::of($akeneoAttributeData->getCode(), $frontendInput, $defaultFrontendLabel);
        return $this->filterOutput($magentoAttributeData);
    }

    public function withTypeToFrontendInputMapper(callable $fn): self
    {
        $result = clone $this;
        $result->typeToFrontendInputMapper = $fn;
        return $result;
    }

    public static function getDefaultTypeToFrontendInputMapper(): callable
    {
        $typeToFrontendInputMap = [
            AttributeType::IDENTIFIER => FrontendInput::TEXT,
            AttributeType::SIMPLESELECT => FrontendInput::SELECT,
            AttributeType::BOOLEAN => FrontendInput::BOOLEAN,
            AttributeType::NUMBER => FrontendInput::TEXT,
            AttributeType::PRICE_COLLECTION => FrontendInput::PRICE,
            AttributeType::DATE => FrontendInput::DATE,
            AttributeType::TEXT => FrontendInput::TEXT,
            AttributeType::TEXTAREA => FrontendInput::TEXTAREA,
            AttributeType::MULTISELECT => FrontendInput::MULTISELECT,
        ];
        return function (string $akeneoType) use ($typeToFrontendInputMap) {
            return $typeToFrontendInputMap[$akeneoType] ?? FrontendInput::TEXT;
        };
    }

    /** @var callable */
    private $typeToFrontendInputMapper;
    private $defaultLocale;

    private function __construct()
    {
        $this->typeToFrontendInputMapper = self::getDefaultTypeToFrontendInputMapper();
    }
}
