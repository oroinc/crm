<?php

namespace Oro\Bundle\SearchBundle\Test\Entity;

use Oro\Bundle\SearchBundle\Entity\Queue;

class QueueTest extends \PHPUnit_Framework_TestCase
{
    public function testEntity()
    {
        $queue = $this->getQueue();
        $this->assertNull($queue->getEntity());

        $queue->setEntity('TestBundle:test');
        $this->assertEquals('TestBundle:test', $queue->getEntity());
    }

    public function testRecordId()
    {
        $queue = $this->getQueue();
        $this->assertNull($queue->getRecordId());

        $queue->setRecordId(2);
        $this->assertEquals(2, $queue->getRecordId());
    }

    public function testEvent()
    {
        $queue = $this->getQueue();
        $this->assertNull($queue->getEvent());

        $queue->setEvent('insert');
        $this->assertEquals('insert', $queue->getEvent());
    }

    /**
     * @return Queue
     */
    protected function getQueue()
    {
        return $this->getMockForAbstractClass('Oro\Bundle\SearchBundle\Entity\Queue');
    }
}
