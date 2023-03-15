<?php

namespace Oro\Bundle\ChannelBundle\Tests\Unit\Provider\Lifetime;

use Doctrine\Common\Annotations\AnnotationReader;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Mapping\Driver\AnnotationDriver;
use Doctrine\Persistence\ManagerRegistry;
use Oro\Bundle\AccountBundle\Entity\Account;
use Oro\Bundle\ChannelBundle\Entity\Channel;
use Oro\Bundle\ChannelBundle\Provider\Lifetime\AmountProvider;
use Oro\Component\Testing\Unit\ORM\OrmTestCase;

class AmountProviderTest extends OrmTestCase
{
    private EntityManagerInterface $em;
    private AmountProvider $provider;

    protected function setUp(): void
    {
        $this->em = $this->getTestEntityManager();
        $this->em->getConfiguration()->setMetadataDriverImpl(new AnnotationDriver(new AnnotationReader()));

        $doctrine = $this->createMock(ManagerRegistry::class);
        $doctrine->expects($this->any())
            ->method('getManagerForClass')
            ->willReturn($this->em);

        $this->provider = new AmountProvider($doctrine);
    }

    /**
     * @dataProvider lifetimeValueProvider
     */
    public function testGetAccountLifetime(string $expectedSQL, float $result, ?Channel $channel)
    {
        $smt = $this->createFetchStatementMock([['sclr_0' => $result]]);
        $this->getDriverConnectionMock($this->em)->expects($this->once())
            ->method('prepare')
            ->with($expectedSQL)
            ->willReturn($smt);

        $account = $this->createMock(Account::class);
        $this->assertSame($result, $this->provider->getAccountLifeTimeValue($account, $channel));
    }

    public function lifetimeValueProvider(): array
    {
        return [
            'get account summary lifetime'    => [
                'SELECT SUM(o0_.amount) AS sclr_0 FROM orocrm_channel_lifetime_hist o0_ ' .
                'LEFT JOIN orocrm_channel o1_ ON o0_.data_channel_id = o1_.id ' .
                'WHERE o0_.account_id = ? AND o0_.status = ? LIMIT 1',
                100.00,
                null
            ],
            'get account lifetime in channel' => [
                'SELECT SUM(o0_.amount) AS sclr_0 FROM orocrm_channel_lifetime_hist o0_ ' .
                'LEFT JOIN orocrm_channel o1_ ON o0_.data_channel_id = o1_.id ' .
                'WHERE o0_.account_id = ? AND o0_.data_channel_id = ? AND o0_.status = ? LIMIT 1',
                100.00,
                $this->createMock(Channel::class)
            ]
        ];
    }
}
