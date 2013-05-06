<?php

namespace Oro\Bundle\ContactBundle\Tests\Unit\Entity\Manager;

use Doctrine\Common\Persistence\ObjectManager;
use Oro\Bundle\FlexibleEntityBundle\Manager\FlexibleManager;
use Oro\Bundle\ContactBundle\Entity\Manager\ContactManager;
use Doctrine\Common\Persistence\ObjectRepository;
use Doctrine\ORM\QueryBuilder;
use Oro\Bundle\ContactBundle\Entity\Contact;

class ContactManagerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var ContactManager
     */
    protected $manager;

    /**
     * @var string
     */
    protected $class = 'Oro\Bundle\ContactBundle\Entity\Contact';

    /**
     * @var ObjectManager
     */
    protected $om;

    /**
     * @var FlexibleManager
     */
    protected $flexManager;

    protected function setUp()
    {
        $this->om = $this->getMockBuilder('Doctrine\ORM\EntityManager')
            ->disableOriginalConstructor()
            ->getMock();
        $classMetadata = $this->getMock('\Doctrine\Common\Persistence\Mapping\ClassMetadata');
        $classMetadata->expects($this->once())
            ->method('getName')
            ->will($this->returnValue($this->class));
        $this->om->expects($this->once())
            ->method('getClassMetadata')
            ->with($this->class)
            ->will($this->returnValue($classMetadata));
        $this->flexManager = $this->getMockBuilder('Oro\Bundle\FlexibleEntityBundle\Manager\FlexibleManager')
            ->disableOriginalConstructor()
            ->getMock();
        $this->manager = new ContactManager($this->class, $this->om, $this->flexManager);
    }

    public function testGetClass()
    {
        $this->assertEquals($this->class, $this->manager->getClass());
    }

    public function testSupportsClass()
    {
        $this->assertTrue($this->manager->supportsClass($this->class));
    }

    public function testSupportsClassNegative()
    {
        $this->assertFalse($this->manager->supportsClass('TestClass'));
    }

    public function testCreateContact()
    {
        $this->assertInstanceOf($this->class, $this->manager->createContact());
    }

    /**
     * @dataProvider doFlushDataProvider
     * @param bool $flush
     */
    public function testUpdateContact($flush)
    {
        /** @var Contact $contact */
        $contact = $this->getMock('Oro\Bundle\ContactBundle\Entity\Contact');
        $this->om->expects($this->once())
            ->method('persist')
            ->with($contact);
        $this->om->expects($this->exactly((int)$flush))
            ->method('flush');
        $this->manager->updateContact($contact, $flush);
    }

    /**
     * @return array
     */
    public function doFlushDataProvider()
    {
        return array(
            array(true),
            array(false)
        );
    }

    public function testDeleteContact()
    {
        /** @var Contact $contact */
        $contact = $this->getMock('Oro\Bundle\ContactBundle\Entity\Contact');
        $this->om->expects($this->once())
            ->method('remove')
            ->with($contact);
        $this->om->expects($this->once())
            ->method('flush');
        $this->manager->deleteContact($contact);
    }

    public function testFindContactBy()
    {
        $criteria = array('id' => 1);
        $result = new \stdClass();
        /** @var ObjectRepository $repository */
        $repository = $this->getMockBuilder('Doctrine\Common\Persistence\ObjectRepository')
            ->disableOriginalConstructor()
            ->getMock();
        $repository->expects($this->once())
            ->method('findOneBy')
            ->with($criteria)
            ->will($this->returnValue($result));
        $this->om->expects($this->once())
            ->method('getRepository')
            ->will($this->returnValue($repository));
        $this->assertEquals($result, $this->manager->findContactBy($criteria));
    }

    public function testFindContacts()
    {
        $result = array();
        /** @var ObjectRepository $repository */
        $repository = $this->getMockBuilder('Doctrine\Common\Persistence\ObjectRepository')
            ->disableOriginalConstructor()
            ->getMock();
        $repository->expects($this->once())
            ->method('findAll')
            ->will($this->returnValue($result));
        $this->om->expects($this->once())
            ->method('getRepository')
            ->will($this->returnValue($repository));
        $this->assertEquals($result, $this->manager->findContacts());
    }

    public function testReloadContact()
    {
        /** @var Contact $contact */
        $contact = $this->getMock('Oro\Bundle\ContactBundle\Entity\Contact');
        $this->om->expects($this->once())
            ->method('refresh')
            ->with($contact);
        $this->manager->reloadContact($contact);
    }

    public function testGetListQuery()
    {
        /** @var QueryBuilder $queryBuilder */
        $queryBuilder = $this->getMockBuilder('Doctrine\ORM\QueryBuilder')
            ->disableOriginalConstructor()
            ->getMock();
        $queryBuilder->expects($this->once())
            ->method('select')
            ->with('c')
            ->will($this->returnSelf());
        $queryBuilder->expects($this->once())
            ->method('from')
            ->with('OroContactBundle:Contact', 'c')
            ->will($this->returnSelf());
        $queryBuilder->expects($this->once())
            ->method('orderBy')
            ->with('c.id', 'ASC')
            ->will($this->returnSelf());
        $this->om->expects($this->once())
            ->method('createQueryBuilder')
            ->will($this->returnValue($queryBuilder));
        $this->assertEquals($queryBuilder, $this->manager->getListQuery());
    }

    public function testCall()
    {
        $result = new \stdClass();
        $this->flexManager->expects($this->once())
            ->method('createFlexible')
            ->will($this->returnValue($result));
        $this->assertEquals($result, $this->manager->createFlexible());
    }

    /**
     * @expectedException \RuntimeException
     * @expectedExceptionMessage Unknown method "asdf"
     */
    public function testCallException()
    {
        $result = new \stdClass();
        $this->flexManager->expects($this->never())
            ->method('asdf');
        $this->assertEquals($result, $this->manager->asdf());
    }
}
