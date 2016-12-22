<?php

namespace Oro\Bundle\ChannelBundle\Tests\Unit\Entity;

use Carbon\Carbon;

use Oro\Bundle\ChannelBundle\Entity\LifetimeValueAverageAggregation;

class LifetimeValueAverageAggregationTest extends AbstractEntityTestCase
{
    /** @var LifetimeValueAverageAggregation */
    protected $entity;

    /**
     * {@inheritDoc}
     */
    public function getEntityFQCN()
    {
        return 'Oro\Bundle\ChannelBundle\Entity\LifetimeValueAverageAggregation';
    }

    /**
     * {@inheritDoc}
     */
    public function getDataProvider()
    {
        $channel         = $this->createMock('Oro\Bundle\ChannelBundle\Entity\Channel');
        $someDateTime    = new \DateTime();
        $someInteger     = 3;
        $someFloat       = 121.12;
        $aggregationDate = \DateTime::createFromFormat(\DateTime::ISO8601, date('Y-m-01\T00:00:00+0000'));

        return [
            'amount'          => ['amount', $someFloat, $someFloat],
            'dataChannel'     => ['dataChannel', $channel, $channel],
            'month'           => ['month', $someInteger, $someInteger],
            'quarter'         => ['quarter', $someInteger, $someInteger],
            'year'            => ['year', $someInteger, $someInteger],
            'aggregationDate' => ['aggregationDate', $someDateTime, $aggregationDate],
        ];
    }

    public function testPrePersist()
    {
        $this->assertNull($this->entity->getAggregationDate());
        $this->assertNull($this->entity->getMonth());
        $this->assertNull($this->entity->getQuarter());
        $this->assertNull($this->entity->getYear());

        $this->entity->prePersist();

        $this->assertInstanceOf('DateTime', $this->entity->getAggregationDate());
        $this->assertNotEmpty($this->entity->getMonth());
        $this->assertNotEmpty($this->entity->getQuarter());
        $this->assertNotEmpty($this->entity->getYear());
    }
}
