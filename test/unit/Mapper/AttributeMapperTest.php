<?php
declare(strict_types=1);
use PHPUnit\Framework\TestCase;

use SnowIO\AkeneoDataModel\AttributeData as AkeneoAttributeData;
use SnowIO\AkeneoDataModel\AttributeType as AkeneoAttributeType;
use SnowIO\AkeneoMagento2Integration\Mapper\AttributeMapper;
use SnowIO\Magento2DataModel\AttributeData as Magento2AttributeData;
use SnowIO\Magento2DataModel\FrontendInput;

class AttributeMapperTest extends TestCase
{
    public function testMap()
    {
        $mapper = AttributeMapper::create('en_GB')->withTypeToFrontendInputMapper(function () {
            return FrontendInput::MULTISELECT;
        });
        $akeneoAttributeData = AkeneoAttributeData::fromJson([
            'code' => 'size',
            'type' => AkeneoAttributeType::SIMPLESELECT,
            'localizable' => true,
            'scopable' => true,
            'sort_order' => 34,
            'labels' => [
                'en_GB' => 'Size',
                'fr_FR' => 'Taille',
            ],
            'group' => 'general',
            '@timestamp' => 1508491122,
        ]);
        $expected = Magento2AttributeData::of('size', FrontendInput::MULTISELECT, 'Size');
        $actual = $mapper->map($akeneoAttributeData);
        self::assertTrue($expected->equals($actual));
    }
}