<?php

namespace Oro\Bundle\ChannelBundle\Tests\Unit\Provider\Lifetime;

use Doctrine\ORM\EntityManager;
use Doctrine\Common\Annotations\AnnotationReader;

use Doctrine\ORM\Mapping\Driver\AnnotationDriver;

use Oro\Bundle\TestFrameworkBundle\Test\Doctrine\ORM\OrmTestCase;
use Oro\Bundle\ChannelBundle\Provider\Lifetime\AmountProvider;

class AmountProviderTest extends OrmTestCase
{
    /** @var EntityManager */
    protected $em;

    /** @var AmountProvider */
    protected $provider;

    protected function setUp()
    {
        $this->em = $this->getTestEntityManager();

        $metadataDriver = new AnnotationDriver(
            new AnnotationReader(),
            'Oro\Bundle\ChannelBundle\Tests\Unit\Stubs\Entity'
        );

        $config = $this->em->getConfiguration();
        $config->setMetadataDriverImpl($metadataDriver);
        $config->setEntityNamespaces(['OroChannelBundle' => 'Oro\Bundle\ChannelBundle\Tests\Unit\Stubs\Entity']);

        $registry = $this->createMock('Symfony\Bridge\Doctrine\RegistryInterface');
        $registry->expects($this->any())
            ->method('getManagerForClass')
            ->will($this->returnValue($this->em));

        $this->provider = new AmountProvider($registry);
    }

    protected function tearDown()
    {
        unset($this->provider, $this->registry);
    }

    /**
     * @dataProvider lifetimeValueProvider
     *
     * @param string $expectedSQL
     * @param string $result
     * @param null   $channel
     */
    public function testGetAccountLifetime($expectedSQL, $result, $channel = null)
    {
        $smt = $this->createFetchStatementMock([['sclr_0' => $result]]);
        $this->getDriverConnectionMock($this->em)
            ->expects($this->once())
            ->method('prepare')
            ->with($expectedSQL)
            ->will($this->returnValue($smt));

        $account = $this->createMock('Oro\Bundle\AccountBundle\Entity\Account');
        $this->assertSame($result, $this->provider->getAccountLifeTimeValue($account, $channel));
    }

    /**
     * @return array
     */
    public function lifetimeValueProvider()
    {
        $channel = $this->createMock('Oro\Bundle\ChannelBundle\Entity\Channel');

        return [
            'get account summary lifetime'    => [
                'SELECT SUM(l0_.amount) AS sclr_0 FROM LifetimeValueHistory l0_ ' .
                'LEFT JOIN Channel c1_ ON l0_.data_channel_id = c1_.id ' .
                'WHERE l0_.account_id = ? AND l0_.status = ? LIMIT 1',
                100.00
            ],
            'get account lifetime in channel' => [
                'SELECT SUM(l0_.amount) AS sclr_0 FROM LifetimeValueHistory l0_ ' .
                'LEFT JOIN Channel c1_ ON l0_.data_channel_id = c1_.id ' .
                'WHERE l0_.account_id = ? AND l0_.data_channel_id = ? AND l0_.status = ? LIMIT 1',
                100.00,
                $channel
            ]
        ];
    }
}
