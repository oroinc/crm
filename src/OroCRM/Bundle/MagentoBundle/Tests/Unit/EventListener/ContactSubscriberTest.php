<?php

namespace OroCRM\Bundle\MagentoBundle\Tests\Unit\EventListener;

use Doctrine\ORM\Event\OnFlushEventArgs;
use Doctrine\ORM\Event\PostFlushEventArgs;

use Oro\Bundle\IntegrationBundle\Entity\Channel;

use OroCRM\Bundle\MagentoBundle\EventListener\ContactSubscriber;
use OroCRM\Bundle\MagentoBundle\Tests\Unit\Fixtures\Entity\ExtendContact;
use OroCRM\Bundle\MagentoBundle\Tests\Unit\Fixtures\Entity\ExtendCustomer;

use OroCRM\Bundle\ContactBundle\Entity\ContactAddress;
use OroCRM\Bundle\ContactBundle\Entity\ContactEmail;
use OroCRM\Bundle\ContactBundle\Entity\ContactPhone;

/**
 * @SuppressWarnings(PHPMD)
 */
class ContactSubscriberTest extends \PHPUnit_Framework_TestCase
{
    /** @var ContactSubscriber */
    protected $subscriber;

    /**  @var \PHPUnit_Framework_MockObject_MockObject */
    protected $securityFacade;

    /**  @var \PHPUnit_Framework_MockObject_MockObject */
    protected $schedulerService;

    /** @var \PHPUnit_Framework_MockObject_MockObject */
    protected $em;

    /** @var \PHPUnit_Framework_MockObject_MockObject */
    protected $uow;

    /** @var OnFlushEventArgs */
    protected $onFlushEventArgs;

    /** @var PostFlushEventArgs */
    protected $postFlushEventArgs;

    public function setUp()
    {
        $this->securityFacade   = $this->getMockBuilder('Oro\Bundle\SecurityBundle\SecurityFacade')
            ->disableOriginalConstructor()
            ->getMock();
        $schedulerServiceLink   = $this
            ->getMockBuilder('Oro\Bundle\EntityConfigBundle\DependencyInjection\Utils\ServiceLink')
            ->disableOriginalConstructor()
            ->getMock();
        $this->schedulerService = $this->getMockBuilder('Oro\Bundle\IntegrationBundle\Manager\SyncScheduler')
            ->disableOriginalConstructor()
            ->getMock();
        $schedulerServiceLink->expects($this->any())
            ->method('getService')
            ->will($this->returnValue($this->schedulerService));

        $this->subscriber = new ContactSubscriber($this->securityFacade, $schedulerServiceLink);

        $this->em  = $this->getMockBuilder('Doctrine\ORM\EntityManager')
            ->disableOriginalConstructor()
            ->getMock();
        $this->uow = $this->getMockBuilder('\Doctrine\ORM\UnitOfWork')
            ->disableOriginalConstructor()
            ->getMock();
        $this->em->expects($this->any())
            ->method('getUnitOfWork')
            ->will($this->returnValue($this->uow));

        $this->onFlushEventArgs   = new OnFlushEventArgs($this->em);
        $this->postFlushEventArgs = new PostFlushEventArgs($this->em);

    }

    public function testGetSubscribedEvents()
    {
        $this->assertEquals(
            ['postFlush', 'onFlush'],
            $this->subscriber->getSubscribedEvents()
        );
    }

    /**
     * @dataProvider dataTest
     *
     * @param       $testContact
     * @param       $testMagentoCustomer
     * @param       $channel
     * @param array $entityInsertions
     * @param array $entityUpdates
     * @param array $entityDeletions
     * @param bool  $isLoggedUser
     * @param bool  $setContactId
     * @param bool  $setMagentoCustomerId
     * @param bool  $entityChangeSetRun
     * @param bool  $scheduleRun
     */
    public function testProcess(
        $testContact,
        $testMagentoCustomer,
        $channel,
        $entityInsertions = [],
        $entityUpdates = [],
        $entityDeletions = [],
        $isLoggedUser = true,
        $setContactId = true,
        $setMagentoCustomerId = true,
        $entityChangeSetRun = true,
        $scheduleRun = true
    ) {
        $repo = $this->getMockBuilder('Doctrine\ORM\EntityRepository')
            ->disableOriginalConstructor()
            ->getMock();
        $this->em->expects($this->any())
            ->method('getRepository')
            ->will($this->returnValue($repo));
        $repo->expects($this->any())
            ->method('findOneBy')
            ->will($this->returnValue($testMagentoCustomer));

        if ($setContactId) {
            $testContact->setId(10);
        } else {
            $testContact->setId(null);
        }
        if ($setMagentoCustomerId) {
            $testMagentoCustomer->setId(125);
        } else {
            $testMagentoCustomer->setId(null);
        }
        $this->uow->expects($this->once())
            ->method('getScheduledEntityInsertions')
            ->will($this->returnValue($entityInsertions));
        $this->uow->expects($this->once())
            ->method('getScheduledEntityUpdates')
            ->will($this->returnValue($entityUpdates));
        $this->uow->expects($this->once())
            ->method('getScheduledEntityDeletions')
            ->will($this->returnValue($entityDeletions));

        $this->securityFacade->expects($this->any())
            ->method('hasLoggedUser')
            ->will($this->returnValue($isLoggedUser));

        if ($entityChangeSetRun) {
            $this->uow->expects($this->once())
                ->method('getEntityChangeSet')
                ->will($this->returnValue(['firstName']));
        } else {
            $this->uow->expects($this->never())
                ->method('getEntityChangeSet');
        }

        if ($scheduleRun) {
            $this->schedulerService->expects($this->once())
                ->method('schedule')
                ->with($channel, 'customer', ['id' => 125]);
        } else {
            $this->schedulerService->expects($this->never())
                ->method('schedule');
        }

        $this->subscriber->onFlush($this->onFlushEventArgs);
        $this->subscriber->postFlush($this->postFlushEventArgs);
    }

    public function dataTest()
    {
        $testContact        = new ExtendContact();
        $testContactAddress = new ContactAddress();
        $testContactEmail   = new ContactEmail();
        $testContactPhone   = new ContactPhone();

        $testContactAddress->setOwner($testContact);
        $testContactEmail->setOwner($testContact);
        $testContactPhone->setOwner($testContact);

        $testMagentoCustomer = new ExtendCustomer();
        $channel             = new Channel();
        $channel->setName('test');
        $testMagentoCustomer->setChannel($channel);

        return [
            'Updated contact'                         => [
                $testContact,
                $testMagentoCustomer,
                $channel,
                [],
                [$testContact],
                [],
                true,
                true,
                true,
                true,
                true
            ],
            'Inserted contact'                        => [
                $testContact,
                $testMagentoCustomer,
                $channel,
                [$testContact],
                [],
                [],
                true,
                false,
                false,
                true,
                false
            ],
            'Update contact from import'              => [
                $testContact,
                $testMagentoCustomer,
                $channel,
                [],
                [$testContact],
                [],
                false,
                false,
                false,
                true,
                false
            ],
            'Deleted contact'                         => [
                $testContact,
                $testMagentoCustomer,
                $channel,
                [],
                [],
                [$testContact],
                true,
                true,
                true,
                false,
                true
            ],
            'Updated contact with testContactAddress' => [
                $testContact,
                $testMagentoCustomer,
                $channel,
                [],
                [$testContact, $testContactAddress],
                [],
                true,
                true,
                true,
                true,
                true
            ],
            'Test process Contact Address'            => [
                $testContact,
                $testMagentoCustomer,
                $channel,
                [],
                [$testContactAddress],
                [],
                true,
                true,
                true,
                false,
                true
            ],
            'Test deleted Contact Address'            => [
                $testContact,
                $testMagentoCustomer,
                $channel,
                [],
                [],
                [$testContactAddress],
                true,
                true,
                true,
                false,
                true
            ],
        ];
    }
}
