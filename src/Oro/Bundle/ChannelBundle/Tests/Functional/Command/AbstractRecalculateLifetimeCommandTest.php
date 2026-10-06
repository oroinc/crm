<?php

declare(strict_types=1);

namespace Oro\Bundle\ChannelBundle\Tests\Functional\Command;

use Oro\Bundle\DataAuditBundle\Test\Functional\AuditRecordsExtension;
use Oro\Bundle\MessageQueueBundle\Test\Functional\MessageQueueExtension;
use Oro\Bundle\TestFrameworkBundle\Test\WebTestCase;
use Oro\Component\Testing\Command\CommandTestingTrait;

abstract class AbstractRecalculateLifetimeCommandTest extends WebTestCase
{
    use AuditRecordsExtension;
    use CommandTestingTrait;
    use MessageQueueExtension;

    protected const string AUDIT_LISTENER = 'oro_dataaudit.listener.send_changed_entities_to_message_queue';

    #[\Override]
    protected function setUp(): void
    {
        $this->initClient();
    }

    /**
     * The lifetime field is not auditable, so recalculating it must not add data audit records.
     */
    public function testThatCommandNotProduceNewDataAuditRecordsInDatabase()
    {
        $this->emptyMessageQueue();

        $lastAuditId = $this->getLastAuditId();
        $lastAuditFieldId = $this->getLastAuditFieldId();

        $this->getOptionalListenerManager()->enableListener(self::AUDIT_LISTENER);

        try {
            $this->doExecuteCommand($this->getCommandName(), ['--force' => true]);

            // Do not assert the number of audit messages: it depends on the batch size
            // {@see SendChangedEntitiesToMessageQueueListener::BATCH_SIZE} and on which fields the
            // listener sends, so it is not stable.
            self::consumeAllMessages();
        } finally {
            // A failed assertion must not leave the listener enabled for the next tests.
            $this->getOptionalListenerManager()->disableListener(self::AUDIT_LISTENER);
        }

        self::assertSame(
            [],
            $this->getAuditFieldsCreatedAfter($lastAuditFieldId),
            'The lifetime recalculation must not add audit field records.'
        );
        self::assertSame(
            [],
            $this->getAuditsCreatedAfter($lastAuditId),
            'The lifetime recalculation must not add audit records.'
        );
    }

    abstract protected function getCommandName(): string;
}
