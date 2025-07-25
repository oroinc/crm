<?php

namespace Oro\Bundle\SalesBundle\Tests\Functional\Command;

use Oro\Bundle\ChannelBundle\Entity\Channel;
use Oro\Bundle\ChannelBundle\Tests\Functional\Command\AbstractRecalculateLifetimeCommandTest;
use Oro\Bundle\SalesBundle\Entity\B2bCustomer;
use Oro\Bundle\SalesBundle\Tests\Functional\DataFixtures\LoadB2bCustomerEntitiesData;

class RecalculateLifetimeCommandTest extends AbstractRecalculateLifetimeCommandTest
{
    #[\Override]
    protected function setUp(): void
    {
        parent::setUp();
        $this->loadFixtures([LoadB2bCustomerEntitiesData::class]);
    }

    #[\Override]
    public function testThatCommandNotProduceNewDataAuditRecordsInDatabase()
    {
        /**
         * @var B2bCustomer $b2bCustomer
         */
        $b2bCustomer = $this->getReference('B2bCustomer_' . LoadB2bCustomerEntitiesData::FIRST_ENTITY_NAME);

        $channel = new Channel();
        $channel->setName('test');
        $channel->setCustomerIdentity('test_identity');
        $channel->setChannelType('b2b');

        $b2bCustomer->setLifetime(-100);
        $b2bCustomer->setDataChannel($channel);

        self::getDataFixturesExecutorEntityManager()->persist($channel);
        self::getDataFixturesExecutorEntityManager()->flush();

        parent::testThatCommandNotProduceNewDataAuditRecordsInDatabase();
    }

    #[\Override]
    protected function getCommandName(): string
    {
        return 'oro:b2b:lifetime:recalculate';
    }
}
