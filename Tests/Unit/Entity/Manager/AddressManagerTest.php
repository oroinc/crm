<?php

namespace Oro\Bundle\AddressBundle\Tests\Entity\Manager;

use Oro\Bundle\AddressBundle\Entity\Manager\AddressManager;
use Oro\Bundle\AddressBundle\Entity\Address;
use Oro\Bundle\FlexibleEntityBundle\Manager\FlexibleManager;
use Doctrine\Common\Persistence\ObjectManager;

class AddressManagerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var ObjectManager
     */
    protected $om;

    /**
     * @var FlexibleManager
     */
    protected $fm;

    public function setUp()
    {
        $this->om = $this->getMock('Doctrine\Common\Persistence\ObjectManager');
        $this->fm = $this->getMockBuilder('Oro\Bundle\FlexibleEntityBundle\Manager\FlexibleManager')
            ->disableOriginalConstructor()
            ->getMock();
    }

    public function testAddressManagerConstruct()
    {
        $class = 'Oro\Bundle\AddressBundle\Entity\Address';

        $classMetaData = $this->getMockBuilder('Doctrine\ORM\Mapping\ClassMetadata')
            ->disableOriginalConstructor()
            ->getMock();
        $classMetaData
            ->expects($this->once())
            ->method('getName')
            ->with()
            ->will($this->returnValue($class));

        $this->om
            ->expects($this->once())
            ->method('getClassMetadata')
            ->with($this->equalTo($class))
            ->will($this->returnValue($classMetaData));

        $addressCriteria = array('street' => 'No way');
        $repository = $this->getMock('Doctrine\Common\Persistence\ObjectRepository')
            ->expects($this->once())
            ->method('findOneBy')
            ->with($this->equalTo($addressCriteria))
            ->will($this->returnValue(new Address()));

        $this->om
            ->expects($this->once())
            ->method('getRepository')
            ->with($this->equalTo($class))
            ->will($this->returnValue($repository));

        $addressManager = new AddressManager($class, $this->om, $this->fm);

        $this->assertEquals($addressManager->getStorageManager(), $this->om);
        $this->assertInstanceOf($class, $addressManager->createAddress());
        $this->assertEquals($class, $addressManager->getClass());

        $addressManager->findAddressBy($addressCriteria);
    }

    public function testGetters()
    {

    }
}
