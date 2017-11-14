<?php
declare(strict_types=1);

namespace SnowIO\AkeneoMagento2\Test;

use PHPUnit\Framework\TestCase;
use SnowIO\AkeneoDataModel\CategoryData as AkeneoCategoryData;
use SnowIO\AkeneoDataModel\CategoryPath;
use SnowIO\AkeneoDataModel\LocalizedString;
use SnowIO\AkeneoMagento2\CategoryMapper;
use SnowIO\Magento2DataModel\CategoryData as Magento2CategoryData;

class CategoryMapperTest extends TestCase
{

    public function testMap()
    {
        $akeneoCategoryData = AkeneoCategoryData::of(CategoryPath::of(['mens', 't_shirts']))
            ->withLabel(LocalizedString::of('Mens T-Shirts', 'en_GB'));
        $expected = Magento2CategoryData::of('t_shirts', 'Mens T-Shirts')
            ->withParentCode('mens');
        $mapper = CategoryMapper::create('en_GB');
        $actual = $mapper($akeneoCategoryData);
        self::assertTrue($expected->equals($actual));
    }

}
