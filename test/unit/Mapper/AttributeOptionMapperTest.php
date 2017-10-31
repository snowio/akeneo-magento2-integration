<?php
declare(strict_types=1);

use PHPUnit\Framework\TestCase;
use SnowIO\AkeneoDataModel\AttributeOption as AkeneoAttributeOption;
use SnowIO\AkeneoDataModel\AttributeOptionIdentifier;
use SnowIO\AkeneoDataModel\LocalizedString;
use SnowIO\AkeneoMagento2Integration\Mapper\AttributeOptionMapper;
use SnowIO\Magento2DataModel\AttributeOption as Magento2AttributeOption;

class AttributeOptionMapperTest extends TestCase
{

    public function testMap()
    {
        $input = AkeneoAttributeOption::of(AttributeOptionIdentifier::of('size', 'large'))
            ->withLabel(LocalizedString::of('Large', 'en_GB'));
        $mapper = AttributeOptionMapper::create('en_GB');
        $actual = $mapper->map($input);
        $expected = Magento2AttributeOption::of('size','large', 'Large');
        self::assertTrue($expected->equals($actual));
     }
}
