<?php

namespace OroCRM\Bundle\ChannelBundle\Tests\Unit\EventListener;

use OroCRM\Bundle\ChannelBundle\EventListener\ChannelDoctrineListener;
use OroCRM\Bundle\ChannelBundle\Tests\Unit\Stubs\Entity\Customer;

class ChannelDoctrineListenerTest extends \PHPUnit_Framework_TestCase
{
    /** @var ChannelDoctrineListener */
    protected $channelDoctrineListener;

    /** @var array */
    protected $fields = [
        'temp1' => [
            'entity' => 'OroCRM\Bundle\ChannelBundle\Tests\Unit\Stubs\Entity\Customer',
            'field'  => 'lifetime',
        ]
    ];

    protected function setUp()
    {
        $settingProvider = $this->getMockBuilder('OroCRM\Bundle\ChannelBundle\Provider\SettingsProvider')
            ->disableOriginalConstructor()->getMock();

        $settingProvider->expects($this->once())
            ->method('getLifetimeValueSettings')
            ->will($this->returnValue($this->fields));

        $this->channelDoctrineListener = new ChannelDoctrineListener($settingProvider);
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

        $account = $this->getMockBuilder('OroCRM\Bundle\AccountBundle\Entity\Account')
            ->disableOriginalConstructor()->getMock();
        $account->expects($this->any())
            ->method('getId')
            ->will($this->returnValue(152));

        $channel = $this->getMockBuilder('OroCRM\Bundle\ChannelBundle\Entity\Channel')
            ->disableOriginalConstructor()->getMock();
        $channel->expects($this->any())
            ->method('getId')
            ->will($this->returnValue(28));

        $customer = new Customer();
        $customer->setAccount($account);
        $customer->setDataChannel($channel);

        $customer1 = clone $customer;
        $customer1->setId(1);

        $customer2 = clone $customer;
        $customer1->setId(2);

        $entities = [
            'hash1' => $customer1,
            'hash2' => $customer2,
        ];

        $this->getMockBuilder('OroCRM\Bundle\MagentoBundle\Model\Customer')->disableOriginalConstructor()->getMock();

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
                $this->assertEquals($entityKey, '152__28');
                $this->assertArrayHasKey('account', $changeset);
                $this->assertArrayHasKey('channel', $changeset);
                $this->assertArrayHasKey('entity', $changeset);
                $this->assertEquals($changeset['account'], 152);
                $this->assertEquals($changeset['channel'], 28);
                $this->assertInstanceOf(
                    'OroCRM\Bundle\ChannelBundle\Tests\Unit\Stubs\Entity\Customer',
                    $changeset['entity']
                );
            }
        }
    }
}
