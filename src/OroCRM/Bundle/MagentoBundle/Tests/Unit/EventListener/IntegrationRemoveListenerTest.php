<?php

namespace OroCRM\Bundle\MagentoBundle\Tests\Unit\EventListener;

use OroCRM\Bundle\MagentoBundle\EventListener\IntegrationRemoveListener;

class IntegrationRemoveListenerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $wsdlManager;

    /**
     * @var IntegrationRemoveListener
     */
    protected $listener;

    protected function setUp()
    {
        $this->wsdlManager = $this->getMockBuilder('OroCRM\Bundle\MagentoBundle\Service\WsdlManager')
            ->disableOriginalConstructor()
            ->getMock();

        $this->listener = new IntegrationRemoveListener($this->wsdlManager);
    }

    protected function tearDown()
    {
        unset($this->listener, $this->wsdlManager);
    }

    /**
     * @dataProvider invalidEntityDataProvider
     * @param object $entity
     */
    public function testIncorrectClass($entity)
    {
        $this->wsdlManager->expects($this->never())
            ->method($this->anything());

        $this->listener->preRemove($this->getEvent($entity));
    }

    /**
     * @return array
     */
    public function invalidEntityDataProvider()
    {
        return [
            [new \stdClass()],
            [$this->getEntity()]
        ];
    }

    public function testPreRemove()
    {
        $url = 'http://test.local';
        $entity = $this->getEntity($url);

        $this->wsdlManager->expects($this->once())
            ->method('clearCacheForUrl')
            ->with($url);

        $this->listener->preRemove($this->getEvent($entity));
    }

    /**
     * @param null|string $url
     * @return \PHPUnit_Framework_MockObject_MockObject
     */
    protected function getEntity($url = null)
    {
        $entity = $this->getMockBuilder('OroCRM\Bundle\MagentoBundle\Entity\MagentoSoapTransport')
            ->disableOriginalConstructor()
            ->getMock();
        $entity->expects($this->atLeastOnce())
            ->method('getWsdlUrl')
            ->will($this->returnValue($url));

        return $entity;
    }

    /**
     * @param object $entity
     * @return \PHPUnit_Framework_MockObject_MockObject
     */
    protected function getEvent($entity)
    {
        $event = $this->getMockBuilder('Doctrine\ORM\Event\LifecycleEventArgs')
            ->disableOriginalConstructor()
            ->getMock();
        $event->expects($this->once())
            ->method('getEntity')
            ->will($this->returnValue($entity));

        return $event;
    }
}
