<?php
declare(strict_types=1);
namespace SnowIO\AkeneoMagento2Integration\Mapper;

use SnowIO\AkeneoDataModel\AttributeData as AkeneoAttributeData;
use SnowIO\AkeneoDataModel\AttributeType;
use SnowIO\Magento2DataModel\AttributeData as Magento2AttributeData;
use SnowIO\Magento2DataModel\FrontendInput;

final class AttributeMapper
{
    public static function create(): self
    {
        $attributeMapper = new self;
        return $attributeMapper;
    }

    public function map(AkeneoAttributeData $attributeData): Magento2AttributeData
    {
        $frontendInput = ($this->typeToFrontendInputMapper)($attributeData->getType());
        $adminLabel = $attributeData->getLabel('en_GB');
        return Magento2AttributeData::of($attributeData->getCode(), $frontendInput, $adminLabel);
    }

    public function withTypeToFrontendInputMap(callable $fn): self
    {
        $result = clone $this;
        $result->typeToFrontendInputMapper = $fn;
        return $result;
    }

    public static function getDefaultTypeToFrontendInputMap(): callable
    {
        $typeToFrontendInputMap = [
            AttributeType::IDENTIFIER => FrontendInput::TEXT,
            AttributeType::SIMPLESELECT => FrontendInput::SELECT,
            AttributeType::BOOLEAN => FrontendInput::BOOLEAN,
            AttributeType::NUMBER => FrontendInput::TEXT,
            AttributeType::PRICE_COLLECTION => FrontendInput::TEXT,
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

    private function __construct()
    {
        $this->typeToFrontendInputMapper = self::getDefaultTypeToFrontendInputMap();
    }
}