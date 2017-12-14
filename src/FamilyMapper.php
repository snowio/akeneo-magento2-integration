<?php
declare(strict_types=1);
namespace SnowIO\AkeneoMagento2;

use Joshdifabio\Transform\CoGbkResult;
use Joshdifabio\Transform\CoGroupByKey;
use Joshdifabio\Transform\FlatMapElements;
use Joshdifabio\Transform\GroupByKey;
use Joshdifabio\Transform\Kv;
use Joshdifabio\Transform\MapElements;
use Joshdifabio\Transform\MapValues;
use Joshdifabio\Transform\Pipeline;
use Joshdifabio\Transform\Transform;
use Joshdifabio\Transform\Values;
use Joshdifabio\Transform\WithKeys;
use SnowIO\AkeneoDataModel\AttributeGroup as AkeneoAttributeGroup;
use SnowIO\AkeneoDataModel\FamilyData;
use SnowIO\AkeneoDataModel\FamilyAttributeData;
use SnowIO\Magento2DataModel\AttributeSet\AttributeData;
use SnowIO\Magento2DataModel\AttributeSet\AttributeDataSet;
use SnowIO\Magento2DataModel\AttributeSet\AttributeGroupData;
use SnowIO\Magento2DataModel\AttributeSet\AttributeGroupDataSet;
use SnowIO\Magento2DataModel\AttributeSetData;
use SnowIO\Magento2DataModel\EntityTypeCode;

final class FamilyMapper extends DataMapper
{
    public static function withDefaultLocale(string $defaultLocale): self
    {
        return new self($defaultLocale);
    }

    public function __invoke(FamilyData $familyData): AttributeSetData
    {
        $groupsWithKeys = WithKeys::of(function (AkeneoAttributeGroup $attributeGroup) {
            return $attributeGroup->getCode();
        })->applyTo($familyData->getGroups());

        $attributesPerGroup = $this->attributeTransform
            ->then(GroupByKey::create())
            ->then(MapValues::via([AttributeDataSet::class, 'of']))
            ->applyTo($familyData->getAttributes());

        $magentoAttributeGroups = Pipeline::of(
            CoGroupByKey::create(),
            Values::get(),
            FlatMapElements::via(function (CoGbkResult $coGbkResult) {
                $group = $coGbkResult->getOptional('group');
                if ($group === null) {
                    return;
                }
                $attributes = $coGbkResult->getOptional('attributes') ?: AttributeDataSet::create();
                yield Kv::of($group, $attributes);
            }),
            $this->attributeGroupTransform
        )->applyTo([
            Kv::of('group', $groupsWithKeys),
            Kv::of('attributes', $attributesPerGroup),
        ]);

        $attributeSetName = $familyData->getLabel($this->defaultLocale);
        if ($attributeSetName === 'Default') {
            $attributeSetName = 'PIM Default';
        }
        return AttributeSetData::of(EntityTypeCode::PRODUCT, $familyData->getCode(), $attributeSetName)
            ->withAttributeGroups(AttributeGroupDataSet::of(\iterator_to_array($magentoAttributeGroups)));
    }

    public function withAttributeGroupTransform(Transform $attributeGroupTransform): self
    {
        $familyMapper = clone $this;
        $familyMapper->attributeGroupTransform = $attributeGroupTransform;
        return $familyMapper;
    }

    public static function getDefaultAttributeGroupTransform(string $defaultLocale): Transform
    {
        return MapElements::via(Kv::unpack(
            function (AkeneoAttributeGroup $attributeGroup, AttributeDataSet $attributes) use ($defaultLocale) {
                $attributeGroupName = $attributeGroup->getLabel($defaultLocale) . ' (Akeneo)';
                return AttributeGroupData::of($attributeGroup->getCode(), $attributeGroupName)
                    ->withSortOrder($attributeGroup->getSortOrder())
                    ->withAttributes($attributes);
            }
        ));
    }

    public function withAttributeTransform(Transform $attributeTransform): self
    {
        $familyMapper = clone $this;
        $familyMapper->attributeTransform = $attributeTransform;
        return $familyMapper;
    }

    public static function getDefaultAttributeTransform(): Transform
    {
        return MapElements::via(function (FamilyAttributeData $akeneoAttributeData): Kv {
            return Kv::of(
                $akeneoAttributeData->getGroup(),
                AttributeData::of($akeneoAttributeData->getCode())
                    ->withSortOrder($akeneoAttributeData->getSortOrder())
            );
        });
    }

    private $defaultLocale;
    private $attributeGroupTransform;
    private $attributeTransform;

    private function __construct(string $defaultLocale)
    {
        $this->defaultLocale = $defaultLocale;
        $this->attributeGroupTransform = self::getDefaultAttributeGroupTransform($defaultLocale);
        $this->attributeTransform = self::getDefaultAttributeTransform();
    }
}
