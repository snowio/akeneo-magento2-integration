<?php
declare(strict_types=1);
namespace SnowIO\AkeneoMagento2;

use SnowIO\AkeneoDataModel\VariantGroupData;
use SnowIO\Magento2DataModel\ProductData;
use SnowIO\Magento2DataModel\ProductStatus;
use SnowIO\Magento2DataModel\ProductTypeId;
use SnowIO\Magento2DataModel\ProductVisibility;

class ConfigurableProductMapper
{
    public static function create(): self
    {
        return new self;
    }

    public function __invoke(VariantGroupData $akeneoVariantGroup)
    {
        $magento2Configurable = ProductData::of(
            $akeneoVariantGroup->getCode(),
            $akeneoVariantGroup->getCode()
        )->withTypeId(ProductTypeId::CONFIGURABLE);

        $visibility = ProductVisibility::CATALOG_SEARCH;
        $customAttributes = ($this->customAttributeMapper)($akeneoVariantGroup->getAttributeValues());
        return $magento2Configurable
            ->withVisibility($visibility)
            ->withCustomAttributes($customAttributes);
    }

    public function withCustomAttributeMapper(callable $customAttributeMapper): self
    {
        $result = clone $this;
        $result->customAttributeMapper = $customAttributeMapper;
        return $result;
    }

    /** @var CustomAttributeMapper */
    private $customAttributeMapper;

    private function __construct()
    {
        $this->customAttributeMapper = CustomAttributeMapper::create();
    }
}
