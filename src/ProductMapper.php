<?php
declare(strict_types=1);
namespace SnowIO\AkeneoMagento2;

use Joshdifabio\Transform\MapElements;
use Joshdifabio\Transform\MapValues;
use Joshdifabio\Transform\Transform;
use Joshdifabio\Transform\WithKeys;
use SnowIO\AkeneoDataModel\AttributeValueSet;
use SnowIO\AkeneoDataModel\ProductData as AkeneoProductData;
use SnowIO\Magento2DataModel\CustomAttributeSet;
use SnowIO\Magento2DataModel\ProductData as Magento2ProductData;
use SnowIO\Magento2DataModel\ProductStatus;
use SnowIO\Magento2DataModel\ProductTypeId;
use SnowIO\Magento2DataModel\ProductVisibility;

final class ProductMapper
{
    public static function create(): self
    {
        return new self;
    }

    public function getTransform(): Transform
    {
        return MapElements::via($this);
    }

    public function getKvTransform(): Transform
    {
        return WithKeys::ofInputElement()->then(MapValues::via($this));
    }

    public function __invoke(AkeneoProductData $akeneoProduct): Magento2ProductData
    {
        $magento2Product = Magento2ProductData::of($akeneoProduct->getSku(), $akeneoProduct->getSku());
        $attributeSetCode = ($this->attributeSetCodeMapper)($akeneoProduct->getProperties()->getFamily());
        if ($attributeSetCode !== null) {
            $magento2Product = $magento2Product->withAttributeSetCode($attributeSetCode);
        }
        $status = $akeneoProduct->getProperties()->getEnabled() ? ProductStatus::ENABLED : ProductStatus::DISABLED;
        $visibility = $akeneoProduct->getProperties()->getVariantGroup() ? ProductVisibility::NOT_VISIBLE_INDIVIDUALLY : ProductVisibility::CATALOG_SEARCH;
        $customAttributes = $this->getCustomAttributeSet($akeneoProduct->getAttributeValues());
        return $magento2Product
            ->withTypeId(ProductTypeId::SIMPLE)
            ->withStatus($status)
            ->withVisibility($visibility)
            ->withCustomAttributes($customAttributes);
    }

    public function withCustomAttributeTransform(Transform $customAttributeTransform): self
    {
        $result = clone $this;
        $result->customAttributeTransform = $customAttributeTransform;
        return $result;
    }

    public function withAttributeSetCodeMapper(callable $fn): self
    {
        $result = clone $this;
        $result->attributeSetCodeMapper = $fn;
        return $result;
    }

    /** @var Transform */
    private $customAttributeTransform;
    /** @var callable */
    private $attributeSetCodeMapper;

    private function __construct()
    {
        $this->customAttributeTransform = CustomAttributeMapper::create()->getTransform();
        $this->attributeSetCodeMapper = function (string $family = null) {
            return $family;
        };
    }

    private function getCustomAttributeSet(AttributeValueSet $akeneoAttributeValues): CustomAttributeSet
    {
        $customAttributesIterator = $this->customAttributeTransform->applyTo($akeneoAttributeValues);
        $customAttributesArray = \iterator_to_array($customAttributesIterator);
        return CustomAttributeSet::of($customAttributesArray);
    }
}
