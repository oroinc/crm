<?php

namespace Oro\Bundle\ChannelBundle\Tests\Unit\Entity;

use Oro\Bundle\ChannelBundle\Entity\Channel;
use Oro\Bundle\ChannelBundle\Entity\LifetimeValueAverageAggregation;
use Oro\Component\Testing\Unit\EntityTestCaseTrait;

class LifetimeValueAverageAggregationTest extends \PHPUnit\Framework\TestCase
{
    use EntityTestCaseTrait;

    public function testProperties()
    {
        $properties = [
            'id'          => ['id', 1],
            'amount'      => ['amount', 121.12],
            'dataChannel' => ['dataChannel', $this->createMock(Channel::class)],
            'month'       => ['month', 2],
            'quarter'     => ['quarter', 3],
            'year'        => ['year', 2020],
        ];

        $entity = new LifetimeValueAverageAggregation();
        self::assertPropertyAccessors($entity, $properties);
    }

    public function testAggregationDate()
    {
        $entity = new LifetimeValueAverageAggregation();
        self::assertNull($entity->getAggregationDate());

        $date = new \DateTime('2020-05-15', new \DateTimeZone('UTC'));
        $entity->setAggregationDate($date);
        self::assertEquals(new \DateTime('2020-05-01', new \DateTimeZone('UTC')), $entity->getAggregationDate());
        self::assertNotSame($date, $entity->getAggregationDate());
    }

    public function testPrePersist()
    {
        $entity = new LifetimeValueAverageAggregation();
        $entity->prePersist();

        self::assertInstanceOf('DateTime', $entity->getAggregationDate());
        self::assertNotEmpty($entity->getYear());
        self::assertNotEmpty($entity->getMonth());
        self::assertNotEmpty($entity->getQuarter());

        $entity->setAggregationDate(new \DateTime('2020-05-15', new \DateTimeZone('UTC')));
        $entity->prePersist();
        self::assertSame(2020, $entity->getYear());
        self::assertSame(5, $entity->getMonth());
        self::assertSame(2, $entity->getQuarter());
    }
}
