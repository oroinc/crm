<?php

namespace OroCRM\Bundle\ChannelBundle\Tests\Unit\EventListener;

use Doctrine\ORM\UnitOfWork;
use Doctrine\ORM\Mapping\Driver\AnnotationDriver;
use Doctrine\Common\Annotations\AnnotationReader;

use Oro\Bundle\TestFrameworkBundle\Test\Doctrine\ORM\Mocks\EntityManagerMock;
use Oro\Bundle\TestFrameworkBundle\Test\Doctrine\ORM\OrmTestCase;

use OroCRM\Bundle\ChannelBundle\EventListener\ChannelDoctrineListener;
use OroCRM\Bundle\ChannelBundle\Tests\Unit\Stubs\Entity\Customer;

class ChannelDoctrineListenerTest extends OrmTestCase
{
    /** @var EntityManagerMock */
    protected $em;

    /** @var ChannelDoctrineListener */
    protected $channelDoctrineListener;

    /** @var UnitOfWork */
    protected $uow;

    /** @var array */
    protected $fields = [
        'temp1' => [
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

        $this->em = $this->getTestEntityManager($conn);

        $this->uow = $this->getMockBuilder('Doctrine\ORM\UnitOfWork')
            ->disableOriginalConstructor()->getMock();
        $this->uow->expects($this->any())->method('commit');
        $this->uow->expects($this->any())->method('persist');

        $this->em->setUnitOfWork($this->uow);

        $metadataDriver = new AnnotationDriver(
            new AnnotationReader(),
            [
                'OroCRM\Bundle\AccountBundle\Entity',
                'OroCRM\Bundle\ChannelBundle\Entity',
                'OroCRM\Bundle\MagentoBundle\Entity',
                'OroCRM\Bundle\ChannelBundle\Tests\Unit\Stubs\Entity',
            ]
        );

        $unitOfWork = $this->getMockBuilder('Doctrine\ORM\UnitOfWork')
            ->disableOriginalConstructor()->getMock();
        $this->em->setUnitOfWork($unitOfWork);

        $config = $this->em->getConfiguration();
        $config->setMetadataDriverImpl($metadataDriver);
        $config->setEntityNamespaces(
            [
                'OroCRMAccountBundle' => 'OroCRM\Bundle\AccountBundle\Entity',
                'OroCRMChannelBundle' => 'OroCRM\Bundle\ChannelBundle\Entity',
                'OroCRMMagentoBundle' => 'OroCRM\Bundle\ChannelBundle\Tests\Unit\Stubs\Entity',
            ]
        );

        $settingProvider = $this->getMockBuilder('OroCRM\Bundle\ChannelBundle\Provider\SettingsProvider')
            ->disableOriginalConstructor()->getMock();

        $settingProvider->expects($this->once())
            ->method('getLifetimeValueSettings')
            ->will($this->returnValue($this->fields));

        $this->channelDoctrineListener = new ChannelDoctrineListener($settingProvider);
    }

    protected function tearDown()
    {
        unset($this->em, $this->channelDoctrineListener);
    }

    public function testOnFlush()
    {
        $args = $this->getMockBuilder('Doctrine\ORM\Event\OnFlushEventArgs')
            ->disableOriginalConstructor()->getMock();

        $em = $this->getMockBuilder('Doctrine\ORM\EntityManager')
            ->disableOriginalConstructor()->getMock();

        $uow = $this->getMockBuilder('Doctrine\ORM\UnitOfWork')
            ->disableOriginalConstructor()->getMock();

        $em->expects($this->once())
            ->method('getUnitOfWork')
            ->will($this->returnValue($uow));

        $args->expects($this->once())
            ->method('getEntityManager')
            ->will($this->returnValue($em));

        $account    = $this->getMockBuilder('OroCRM\Bundle\AccountBundle\Entity\Account')
            ->disableOriginalConstructor()->getMock();
        $account_id = spl_object_hash($account);
        $account->expects($this->any())
            ->method('getId')
            ->will($this->returnValue($account_id));

        $channel    = $this->getMockBuilder('OroCRM\Bundle\ChannelBundle\Entity\Channel')
            ->disableOriginalConstructor()->getMock();
        $channel_id = spl_object_hash($channel);
        $channel->expects($this->any())
            ->method('getId')
            ->will($this->returnValue($channel_id));

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

        $uow->expects($this->once())
            ->method('getScheduledEntityInsertions')
            ->will($this->returnValue($entities));
        $uow->expects($this->once())
            ->method('getScheduledEntityDeletions')
            ->will($this->returnValue([]));
        $uow->expects($this->once())
            ->method('getScheduledEntityUpdates')
            ->will($this->returnValue([]));
        $uow->expects($this->once())
            ->method('getScheduledCollectionDeletions')
            ->will($this->returnValue([]));
        $uow->expects($this->once())
            ->method('getScheduledCollectionUpdates')
            ->will($this->returnValue([]));

        $this->channelDoctrineListener->onFlush($args);

        $reflectionProperty = new \ReflectionProperty(get_class($this->channelDoctrineListener), 'queued');
        $reflectionProperty->setAccessible(true);
        $field = $reflectionProperty->getValue($this->channelDoctrineListener);

        foreach ($field as $entity => $value) {
            $this->assertEquals($entity, 'OroCRM\Bundle\ChannelBundle\Tests\Unit\Stubs\Entity\Customer');
            foreach ($value as $entityKey => $changeset) {
                $this->assertEquals($entityKey, $account_id . '__' . $channel_id);
                $this->assertArrayHasKey('account', $changeset);
                $this->assertArrayHasKey('channel', $changeset);
                $this->assertEquals($changeset['account'], $account_id);
                $this->assertEquals($changeset['channel'], $channel_id);
            }
        }
    }

    public function testPostFlush()
    {
        $args = $this->getMockBuilder('Doctrine\ORM\Event\PostFlushEventArgs')
            ->disableOriginalConstructor()->getMock();
        $args->expects($this->once())
            ->method('getEntityManager')
            ->will($this->returnValue($this->em));

        $account = $this->getMockBuilder('OroCRM\Bundle\AccountBundle\Entity\Account')
            ->disableOriginalConstructor()->getMock();
        $channel = $this->getMockBuilder('OroCRM\Bundle\ChannelBundle\Entity\Channel')
            ->disableOriginalConstructor()->getMock();

        $queue = [
            'OroCRM\Bundle\ChannelBundle\Tests\Unit\Stubs\Entity\Customer' => [
                "1__2" => [
                    'account' => $account,
                    'channel' => $channel,
                ]
            ]
        ];

        $smt = $this->createStatementMock([['sclr0' => [100]]]);

        $this->getDriverConnectionMock($this->em)
            ->expects($this->any())
            ->method('prepare')
            ->will($this->returnValue($smt));

        $this->em->setUnitOfWork($this->uow);

        $reflectionProperty = new \ReflectionProperty(get_class($this->channelDoctrineListener), 'queued');
        $reflectionProperty->setAccessible(true);
        $reflectionProperty->setValue($this->channelDoctrineListener, $queue);

        $this->channelDoctrineListener->postFlush($args);
    }

    /**
     * @param array $records
     *
     * @return \PHPUnit_Framework_MockObject_MockObject
     */
    protected function createStatementMock(array $records)
    {
        $statement = $this->getMock('Oro\Bundle\TestFrameworkBundle\Test\Doctrine\ORM\Mocks\StatementMock');
        $statement->expects($this->exactly(count($records)))
            ->method('fetchAll')
            ->will(
                new \PHPUnit_Framework_MockObject_Stub_ConsecutiveCalls(
                    array_merge($records, [false])
                )
            );

        $statement->expects($this->exactly(count($records) - 1))
            ->method('fetch')
            ->will(
                new \PHPUnit_Framework_MockObject_Stub_ConsecutiveCalls(
                    array_merge($records, [false])
                )
            );

        $statement->expects($this->exactly(count($records)))
            ->method('rowCount')
            ->will($this->returnValue(1));

        return $statement;
    }
}
