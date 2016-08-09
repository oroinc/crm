<?php

namespace OroCRM\Bundle\SalesBundle\Tests\Unit\ImportExport\EventListener;

use Oro\Bundle\ImportExportBundle\Context\ContextInterface;
use Oro\Bundle\ImportExportBundle\Event\StrategyEvent;
use Oro\Bundle\ImportExportBundle\Strategy\StrategyInterface;
use Oro\Bundle\OrganizationBundle\Entity\Organization;
use Oro\Bundle\SecurityBundle\SecurityFacade;

use OroCRM\Bundle\ChannelBundle\Entity\Channel;
use OroCRM\Bundle\SalesBundle\Entity\B2bCustomer;
use OroCRM\Bundle\SalesBundle\Entity\Opportunity;
use OroCRM\Bundle\SalesBundle\ImportExport\EventListener\OpportunityListener;

class OpportunityListenerTest extends \PHPUnit_Framework_TestCase
{
    public function testOnProcessAfter()
    {
        /** @var StrategyInterface $strategy */
        $strategy = $this->getMock('Oro\Bundle\ImportExportBundle\Strategy\StrategyInterface');
        /** @var ContextInterface $context */
        $context      = $this->getMock('Oro\Bundle\ImportExportBundle\Context\ContextInterface');

        /** @var \PHPUnit_Framework_MockObject_MockObject|SecurityFacade $securityFacade */
        $securityFacade = $this->getMockBuilder('Oro\Bundle\SecurityBundle\SecurityFacade')
            ->disableOriginalConstructor()
            ->getMock();

        $securityFacade->expects($this->once())
            ->method('isGranted')
            ->with('CREATE', 'Entity:OroCRM\Bundle\AccountBundle\Entity\Account')
            ->willReturn(true);

        $organization = new Organization();
        $channel      = new Channel();
        $b2bCustomer  = new B2bCustomer();
        $entity       = new Opportunity();

        $b2bCustomerName = 'test_name';
        $b2bCustomer->setName($b2bCustomerName);
        $entity->setDataChannel($channel);
        $entity->setOrganization($organization);
        $entity->setCustomer($b2bCustomer);

        $strategyEvent = new StrategyEvent($strategy, $entity, $context);
        $listener      = new OpportunityListener($securityFacade);
        $listener->onProcessAfter($strategyEvent);

        $this->assertSame($channel, $b2bCustomer->getDataChannel());
        $this->assertSame($organization, $b2bCustomer->getOrganization());
        $this->assertEquals($b2bCustomerName, $b2bCustomer->getAccount()->getName());
    }
}
