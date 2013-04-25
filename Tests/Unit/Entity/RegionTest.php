<?php

namespace Oro\Bundle\AddressBundle\Tests\Entity;

use Oro\Bundle\AddressBundle\Entity\Region;

class RegionTest extends \PHPUnit_Framework_TestCase
{
    public function testSettersAndGetters()
    {
        $obj = new Region();

        $obj->setName('test name');
        $this->assertEquals('test name', $obj->getName());

        $obj->setCode('test code');
        $this->assertEquals('test code', $obj->getCode());

        $countryMock = $this->getMockBuilder('Oro\Bundle\AddressBundle\Entity\Country')
            ->disableOriginalConstructor()
            ->getMock();
        $obj->setCountry($countryMock);
        $this->assertEquals($countryMock, $obj->getCountry());
    }

    public function testConstructorData()
    {
        $obj = new Region();

        $this->assertNull($obj->getId());
        $obj->setLocale();
    }
}
