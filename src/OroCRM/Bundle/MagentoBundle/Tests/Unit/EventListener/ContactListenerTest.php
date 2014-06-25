<?php

namespace OroCRM\Bundle\MagentoBundle\Tests\Unit\EventListener;

use Doctrine\ORM\Event\OnFlushEventArgs;
use Doctrine\ORM\Event\PostFlushEventArgs;

use Oro\Bundle\IntegrationBundle\Entity\Channel;

use OroCRM\Bundle\MagentoBundle\EventListener\ContactListener;
use OroCRM\Bundle\MagentoBundle\Tests\Unit\Fixtures\Entity\ExtendContact;
use OroCRM\Bundle\MagentoBundle\Tests\Unit\Fixtures\Entity\ExtendCustomer;

use OroCRM\Bundle\ContactBundle\Entity\ContactAddress;
use OroCRM\Bundle\ContactBundle\Entity\ContactEmail;
use OroCRM\Bundle\ContactBundle\Entity\ContactPhone;

/**
 * @SuppressWarnings(PHPMD)
 */
class ContactListenerTest extends \PHPUnit_Framework_TestCase
{
    /** @var ContactListener */
    protected $listener;

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
        $securityFacadeLink   = $this
            ->getMockBuilder('Oro\Bundle\EntityConfigBundle\DependencyInjection\Utils\ServiceLink')
            ->disableOriginalConstructor()
            ->getMock();
        $this->securityFacade   = $this->getMockBuilder('Oro\Bundle\SecurityBundle\SecurityFacade')
            ->disableOriginalConstructor()
            ->getMock();
        $securityFacadeLink->expects($this->any())
            ->method('getService')
            ->will($this->returnValue($this->securityFacade));
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

        $this->listener = new ContactListener($securityFacadeLink, $schedulerServiceLink);

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
        $this->uow->expects($this->once())
            ->method('getScheduledCollectionUpdates')
            ->will($this->returnValue([]));
        $this->uow->expects($this->once())
            ->method('getScheduledCollectionDeletions')
            ->will($this->returnValue([]));

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
                ->with($channel, 'customer', ['id' => 125], false);
        } else {
            $this->schedulerService->expects($this->never())
                ->method('schedule');
        }

        $this->listener->onFlush($this->onFlushEventArgs);
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
        $channel->getSynchronizationSettingsReference()->offsetSet('isTwoWaySyncEnabled', true);
        $channel->setName('test');
        $channel->setEnabled(true);
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

    public function testUpdateContactFromImport()
    {
        $this->uow->expects($this->never())
            ->method('getScheduledEntityInsertions');
        $this->uow->expects($this->never())
            ->method('getScheduledEntityUpdates');
        $this->uow->expects($this->never())
            ->method('getScheduledEntityDeletions');

        $this->securityFacade->expects($this->any())
            ->method('hasLoggedUser')
            ->will($this->returnValue(false));

        $this->listener->onFlush($this->onFlushEventArgs);
    }
}
