<?php

namespace OroCRM\Bundle\MarketingListBundle\Tests\Unit\Model;

use OroCRM\Bundle\MarketingListBundle\Model\MarketingListItemConnector;

class MarketingListItemConnectorTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $registry;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $doctrineHelper;

    /**
     * @var MarketingListItemConnector
     */
    protected $connector;

    protected function setUp()
    {
        $this->registry = $this->getMockBuilder('Symfony\Bridge\Doctrine\ManagerRegistry')
            ->disableOriginalConstructor()
            ->getMock();
        $this->doctrineHelper = $this->getMockBuilder('Oro\Bundle\EntityBundle\ORM\DoctrineHelper')
            ->disableOriginalConstructor()
            ->getMock();
        $this->connector = new MarketingListItemConnector($this->registry, $this->doctrineHelper);
    }

    protected function tearDown()
    {
        unset($this->connector);
        unset($this->doctrineHelper);
        unset($this->registry);
    }

    public function testContactExisting()
    {
        $entityId = 42;
        $marketingList = $this->getMockBuilder('OroCRM\Bundle\MarketingListBundle\Entity\MarketingList')
            ->disableOriginalConstructor()
            ->getMock();
        $marketingListItem = $this->assertContactedExisting($marketingList, $entityId);

        $this->assertEquals($marketingListItem, $this->connector->contact($marketingList, $entityId));
    }

    public function testContactNew()
    {
        $entityId = 42;
        $marketingList = $this->getMockBuilder('OroCRM\Bundle\MarketingListBundle\Entity\MarketingList')
            ->disableOriginalConstructor()
            ->getMock();

        $repository = $this->getMockBuilder('\Doctrine\Common\Persistence\ObjectRepository')
            ->disableOriginalConstructor()
            ->getMock();
        $repository->expects($this->once())
            ->method('findOneBy')
            ->with(array('marketingList' => $marketingList, 'entityId' => $entityId))
            ->will($this->returnValue(null));
        $this->registry->expects($this->once())
            ->method('getRepository')
            ->with(MarketingListItemConnector::MARKETING_LIST_ITEM_ENTITY)
            ->will($this->returnValue($repository));

        $em = $this->getMockBuilder('\Doctrine\Common\Persistence\ObjectManager')
            ->disableOriginalConstructor()
            ->getMock();
        $em->expects($this->once())
            ->method('persist')
            ->with($this->isInstanceOf('OroCRM\Bundle\MarketingListBundle\Entity\MarketingListItem'));
        $this->registry->expects($this->once())
            ->method('getManagerForClass')
            ->with(MarketingListItemConnector::MARKETING_LIST_ITEM_ENTITY)
            ->will($this->returnValue($em));

        $marketingListItem = $this->connector->contact($marketingList, $entityId);
        $this->assertInstanceOf(
            'OroCRM\Bundle\MarketingListBundle\Entity\MarketingListItem',
            $marketingListItem
        );

        $this->assertEquals(1, $marketingListItem->getContactedTimes());
        $this->assertInstanceOf('\DateTime', $marketingListItem->getLastContactedAt());
    }

    public function testContactResultRow()
    {
        $entityId = 42;
        $marketingList = $this->getMockBuilder('OroCRM\Bundle\MarketingListBundle\Entity\MarketingList')
            ->disableOriginalConstructor()
            ->getMock();
        $marketingList->expects($this->once())
            ->method('getEntity')
            ->will($this->returnValue('\stdClass'));
        $this->doctrineHelper->expects($this->once())
            ->method('getSingleEntityIdentifierFieldName')
            ->with('\stdClass')
            ->will($this->returnValue('id'));

        $this->assertContactedExisting($marketingList, $entityId);
        $marketingListItem = $this->connector->contactResultRow($marketingList, array('id' => $entityId));
        $this->assertInstanceOf(
            'OroCRM\Bundle\MarketingListBundle\Entity\MarketingListItem',
            $marketingListItem
        );
    }

    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage Result row must contain identifier field
     */
    public function testContactResultRowException()
    {
        $entityId = 42;
        $marketingList = $this->getMockBuilder('OroCRM\Bundle\MarketingListBundle\Entity\MarketingList')
            ->disableOriginalConstructor()
            ->getMock();
        $marketingList->expects($this->once())
            ->method('getEntity')
            ->will($this->returnValue('\stdClass'));
        $this->doctrineHelper->expects($this->once())
            ->method('getSingleEntityIdentifierFieldName')
            ->with('\stdClass')
            ->will($this->returnValue('id'));
        $this->connector->contactResultRow($marketingList, array('some' => $entityId));
    }

    public function assertContactedExisting($marketingList, $entityId)
    {
        $marketingListItem = $this->getMockBuilder('OroCRM\Bundle\MarketingListBundle\Entity\MarketingListItem')
            ->disableOriginalConstructor()
            ->getMock();
        $marketingListItem->expects($this->once())
            ->method('contact');

        $repository = $this->getMockBuilder('\Doctrine\Common\Persistence\ObjectRepository')
            ->disableOriginalConstructor()
            ->getMock();
        $repository->expects($this->once())
            ->method('findOneBy')
            ->with(array('marketingList' => $marketingList, 'entityId' => $entityId))
            ->will($this->returnValue($marketingListItem));
        $this->registry->expects($this->once())
            ->method('getRepository')
            ->with(MarketingListItemConnector::MARKETING_LIST_ITEM_ENTITY)
            ->will($this->returnValue($repository));

        return $marketingListItem;
    }
}
