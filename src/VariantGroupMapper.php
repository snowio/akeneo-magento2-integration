<?php
declare(strict_types=1);
namespace SnowIO\AkeneoMagento2;

use Joshdifabio\Transform\MapElements;
use Joshdifabio\Transform\MapValues;
use Joshdifabio\Transform\Transform;
use Joshdifabio\Transform\WithKeys;
use SnowIO\AkeneoDataModel\AttributeValueSet;
use SnowIO\AkeneoDataModel\VariantGroupData;
use SnowIO\Magento2DataModel\CustomAttributeSet;
use SnowIO\Magento2DataModel\ProductData;
use SnowIO\Magento2DataModel\ProductTypeId;
use SnowIO\Magento2DataModel\ProductVisibility;

class VariantGroupMapper
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

    public function __invoke(VariantGroupData $akeneoVariantGroup): ProductData
    {
        $customAttributes = $this->getCustomAttributeSet($akeneoVariantGroup->getAttributeValues());
        return ProductData::of($akeneoVariantGroup->getCode(), $akeneoVariantGroup->getCode())
            ->withTypeId(ProductTypeId::CONFIGURABLE)
            ->withVisibility(ProductVisibility::CATALOG_SEARCH)
            ->withCustomAttributes($customAttributes);
    }

    public function withCustomAttributeTransform(Transform $customAttributeTransform): self
    {
        $result = clone $this;
        $result->customAttributeTransform = $customAttributeTransform;
        return $result;
    }

    /** @var Transform */
    private $customAttributeTransform;

    private function __construct()
    {
        $this->customAttributeTransform = CustomAttributeMapper::create()->getTransform();
    }

    private function getCustomAttributeSet(AttributeValueSet $akeneoAttributeValues): CustomAttributeSet
    {
        $customAttributesIterator = $this->customAttributeTransform->applyTo($akeneoAttributeValues);
        $customAttributesArray = \iterator_to_array($customAttributesIterator);
        return CustomAttributeSet::of($customAttributesArray);
    }
}
