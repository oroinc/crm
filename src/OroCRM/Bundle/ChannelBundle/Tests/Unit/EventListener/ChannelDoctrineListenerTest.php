<?php

namespace OroCRM\Bundle\ChannelBundle\Tests\Unit\EventListener;

use Doctrine\ORM\Event\PostFlushEventArgs;
use Doctrine\ORM\UnitOfWork;
use Doctrine\ORM\Event\OnFlushEventArgs;
use Doctrine\ORM\Mapping\Driver\AnnotationDriver;
use Doctrine\Common\Annotations\AnnotationReader;

use Oro\Bundle\TestFrameworkBundle\Test\Doctrine\ORM\OrmTestCase;
use Oro\Bundle\TestFrameworkBundle\Test\Doctrine\ORM\Mocks\EntityManagerMock;

use OroCRM\Bundle\ChannelBundle\EventListener\ChannelDoctrineListener;
use OroCRM\Bundle\ChannelBundle\Tests\Unit\Stubs\Entity\Customer;

class ChannelDoctrineListenerTest extends OrmTestCase
{
    const TEST_CHANNEL_ID = 1;
    const TEST_ACCOUNT_ID = 112;

    /** @var EntityManagerMock */
    protected $em;

    /** @var UnitOfWork|\PHPUnit_Framework_MockObject_MockObject */
    protected $uow;

    /** @var ChannelDoctrineListener */
    protected $channelDoctrineListener;

    /** @var array */
    protected $settings = [
        'someChannelType' => [
            'entity' => 'OroCRM\Bundle\ChannelBundle\Tests\Unit\Stubs\Entity\Customer',
            'field'  => 'lifetime',
        ]
    ];

    protected function setUp()
    {
        $conn = [
            'driverClass'  => 'Oro\Bundle\TestFrameworkBundle\Test\Doctrine\ORM\Mocks\DriverMock',
            'wrapperClass' => 'OroCRM\Bundle\ChannelBundle\Tests\Unit\Stubs\Model\ConnectionMock',
            'user'         => 'john',
            'password'     => 'wayne'
        ];

        $metadataDriver = new AnnotationDriver(
            new AnnotationReader(),
            ['OroCRM\Bundle\ChannelBundle\Tests\Unit\Stubs\Entity']
        );

        $this->uow = $this->getMockBuilder('Doctrine\ORM\UnitOfWork')
            ->disableOriginalConstructor()->getMock();
        $this->em  = $this->getTestEntityManager($conn);
        $this->em->setUnitOfWork($this->uow);

        $config = $this->em->getConfiguration();
        $config->setMetadataDriverImpl($metadataDriver);
        $config->setEntityNamespaces(
            [
                'OroCRMAccountBundle' => 'OroCRM\Bundle\ChannelBundle\Tests\Unit\Stubs\Entity',
                'OroCRMChannelBundle' => 'OroCRM\Bundle\ChannelBundle\Tests\Unit\Stubs\Entity',
                'OroCRMMagentoBundle' => 'OroCRM\Bundle\ChannelBundle\Tests\Unit\Stubs\Entity',
            ]
        );

        $settingProvider = $this->getMockBuilder('OroCRM\Bundle\ChannelBundle\Provider\SettingsProvider')
            ->disableOriginalConstructor()->getMock();
        $settingProvider->expects($this->once())->method('getLifetimeValueSettings')
            ->will($this->returnValue($this->settings));

        $this->channelDoctrineListener = new ChannelDoctrineListener($settingProvider);
    }

    protected function tearDown()
    {
        unset($this->em, $this->uow, $this->channelDoctrineListener);
    }

    public function testOnFlush()
    {
        $args = new OnFlushEventArgs($this->em);

        $account = $this->getMock('OroCRM\Bundle\AccountBundle\Entity\Account');
        $account->expects($this->any())->method('getId')
            ->will($this->returnValue(self::TEST_ACCOUNT_ID));
        $channel = $this->getMock('OroCRM\Bundle\ChannelBundle\Entity\Channel');
        $channel->expects($this->any())->method('getId')
            ->will($this->returnValue(self::TEST_CHANNEL_ID));

        $customer = new Customer();
        $customer->setAccount($account);
        $customer->setDataChannel($channel);
        $customer->setId(1);

        $customer1 = clone $customer;
        $customer1->setId(2);

        $entities = [
            'hash1' => $customer,
            'hash2' => $customer1,
        ];

        $this->uow->expects($this->once())->method('getScheduledEntityInsertions')
            ->will($this->returnValue($entities));
        $this->uow->expects($this->once())->method('getScheduledEntityDeletions')
            ->will($this->returnValue([]));
        $this->uow->expects($this->once())->method('getScheduledEntityUpdates')
            ->will($this->returnValue([]));
        $this->uow->expects($this->once())->method('getScheduledCollectionDeletions')
            ->will($this->returnValue([]));
        $this->uow->expects($this->once())->method('getScheduledCollectionUpdates')
            ->will($this->returnValue([]));

        $this->channelDoctrineListener->onFlush($args);

        $queued = $this->readAttribute($this->channelDoctrineListener, 'queued');
        foreach ($queued as $entity => $value) {
            $this->assertEquals($entity, 'OroCRM\Bundle\ChannelBundle\Tests\Unit\Stubs\Entity\Customer');

            foreach ($value as $entityKey => $changeSet) {
                $this->assertArrayHasKey('account', $changeSet);
                $this->assertArrayHasKey('channel', $changeSet);
                $this->assertEquals($changeSet['account'], self::TEST_ACCOUNT_ID);
                $this->assertEquals($changeSet['channel'], self::TEST_CHANNEL_ID);
            }
        }
    }

    public function testPostFlush()
    {
        $args = new PostFlushEventArgs($this->em);

        $account  = $this->getMock('OroCRM\Bundle\AccountBundle\Entity\Account');
        $channel  = $this->getMock('OroCRM\Bundle\ChannelBundle\Entity\Channel');
        $account2 = clone $account;

        $queue = [
            'OroCRM\Bundle\ChannelBundle\Tests\Unit\Stubs\Entity\Customer' => [
                uniqid('accountId__channelId') => ['account' => $account, 'channel' => $channel],
                uniqid('accountId__channelId') => ['account' => $account2, 'channel' => $channel],
            ]
        ];

        $this->setUpDatabaseQueriesAssertions($this->em);
        $this->uow->expects($this->exactly(2))->method('persist');
        $this->uow->expects($this->once())->method('commit');

        $reflectionProperty = new \ReflectionProperty(get_class($this->channelDoctrineListener), 'queued');
        $reflectionProperty->setAccessible(true);
        $reflectionProperty->setValue($this->channelDoctrineListener, $queue);

        $this->channelDoctrineListener->postFlush($args);
    }

    /**
     * @param EntityManagerMock $em
     */
    private function setUpDatabaseQueriesAssertions(EntityManagerMock $em)
    {
        $selectLifetimeSmt = $this->getMock('Oro\Bundle\TestFrameworkBundle\Test\Doctrine\ORM\Mocks\StatementMock');
        $selectLifetimeSmt->expects($this->exactly(2))->method('fetchAll')
            ->will(
                $this->onConsecutiveCalls(
                    [['sclr0' => [100]]],
                    [['sclr0' => [200]]]
                )
            );

        $updateSmt = $this->getMock('Oro\Bundle\TestFrameworkBundle\Test\Doctrine\ORM\Mocks\StatementMock');
        $updateSmt->expects($this->once())->method('rowCount')
            ->will($this->returnValue(1));

        $this->getDriverConnectionMock($em)->expects($this->any())
            ->method('prepare')
            ->will(
                $this->returnValueMap(
                    [
                        [
                            'SELECT SUM(c0_.lifetime) AS sclr0 FROM Customer c0_ ' .
                            'WHERE c0_.account_id = ? AND c0_.channel_id = ?',
                            $selectLifetimeSmt
                        ],
                        [
                            'UPDATE LifetimeValueHistory SET status = 0 ' .
                            'WHERE account_id IN (?, ?) AND data_channel_id = ?',
                            $updateSmt
                        ]
                    ]
                )
            );
    }
}
