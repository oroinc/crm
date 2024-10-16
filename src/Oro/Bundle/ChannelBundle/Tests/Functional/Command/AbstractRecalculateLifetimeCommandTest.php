<?php

namespace Oro\Bundle\ChannelBundle\Tests\Functional\Command;

use Oro\Bundle\DataAuditBundle\Entity\Audit;
use Oro\Bundle\DataAuditBundle\Entity\AuditField;
use Oro\Bundle\MessageQueueBundle\Test\Functional\MessageQueueExtension;
use Oro\Bundle\TestFrameworkBundle\Test\WebTestCase;
use Oro\Component\Testing\Command\CommandTestingTrait;

abstract class AbstractRecalculateLifetimeCommandTest extends WebTestCase
{
    use CommandTestingTrait;
    use MessageQueueExtension;

    #[\Override]
    protected function setUp(): void
    {
        $this->initClient();
    }

    public function testThatCommandNotProduceNewDataAuditRecordsInDatabase()
    {
        $manager = self::getDataFixturesExecutorEntityManager();

        $this->getOptionalListenerManager()->enableListener(
            'oro_dataaudit.listener.send_changed_entities_to_message_queue'
        );

        $this->doExecuteCommand($this->getCommandName(), ['--force' => true]);

        self::consumeAllMessages();

        $this->assertEmpty($manager->getRepository(Audit::class)->findAll());
        $this->assertEmpty($manager->getRepository(AuditField::class)->findAll());

        $this->getOptionalListenerManager()->disableListener(
            'oro_dataaudit.listener.send_changed_entities_to_message_queue'
        );
    }

    abstract protected function getCommandName(): string;
}
