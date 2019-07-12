<?php

namespace Oro\Bundle\SalesBundle\Tests\Unit\ImportExport\EventListener;

use Oro\Bundle\AccountBundle\Entity\Account;
use Oro\Bundle\ChannelBundle\Entity\Channel;
use Oro\Bundle\CurrencyBundle\Provider\CurrencyProviderInterface;
use Oro\Bundle\ImportExportBundle\Context\ContextInterface;
use Oro\Bundle\ImportExportBundle\Event\StrategyEvent;
use Oro\Bundle\ImportExportBundle\Strategy\StrategyInterface;
use Oro\Bundle\OrganizationBundle\Entity\Organization;
use Oro\Bundle\SalesBundle\Builder\OpportunityRelationsBuilder;
use Oro\Bundle\SalesBundle\Entity\B2bCustomer;
use Oro\Bundle\SalesBundle\ImportExport\EventListener\OpportunityListener;
use Oro\Bundle\SalesBundle\Tests\Unit\Fixture\CustomerStub;
use Oro\Bundle\SalesBundle\Tests\Unit\Fixture\OpportunityStub as Opportunity;

class OpportunityListenerTest extends \PHPUnit\Framework\TestCase
{
    public function testOnProcessAfter()
    {
        /** @var StrategyInterface $strategy */
        $strategy = $this->createMock('Oro\Bundle\ImportExportBundle\Strategy\StrategyInterface');
        /** @var ContextInterface $context */
        $context      = $this->createMock('Oro\Bundle\ImportExportBundle\Context\ContextInterface');

        $currencyProvider = $this
            ->getMockBuilder(CurrencyProviderInterface::class)
            ->disableOriginalConstructor()
            ->setMethods(['getCurrencyList', 'getDefaultCurrency'])
            ->getMock();

        $translator = $this->createMock('Symfony\Contracts\Translation\TranslatorInterface');
        $importStrategyHelper = $this->createMock('Oro\Bundle\ImportExportBundle\Strategy\Import\ImportStrategyHelper');

        $organization = new Organization();
        $channel      = new Channel();
        $b2bCustomer  = new B2bCustomer();
        $entity       = new Opportunity();
        $accountCustomer = new CustomerStub();

        $b2bCustomerName = 'test_name';
        $b2bCustomer->setName($b2bCustomerName);
        $accountCustomer->setTarget(new Account(), $b2bCustomer);

        $entity->setDataChannel($channel);
        $entity->setOrganization($organization);
        $entity->setCustomerAssociation($accountCustomer);

        $strategyEvent = new StrategyEvent($strategy, $entity, $context);
        $listener      = new OpportunityListener(
            new OpportunityRelationsBuilder(),
            $currencyProvider,
            $translator,
            $importStrategyHelper
        );
        $listener->onProcessAfter($strategyEvent);

        $this->assertSame($organization, $b2bCustomer->getOrganization());
        $this->assertEquals($b2bCustomerName, $b2bCustomer->getAccount()->getName());
    }
}
