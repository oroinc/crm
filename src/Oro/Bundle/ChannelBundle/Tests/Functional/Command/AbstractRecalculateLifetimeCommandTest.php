<?php

declare(strict_types=1);

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
        $manager = self::getDataFixturesExecutorEntityManager();

        $this->emptyMessageQueue();

        $auditFieldCount = $manager->getRepository(AuditField::class)->count([]);
        $auditCount = $manager->getRepository(Audit::class)->count([]);
        $lastAuditId = $this->getLastAuditId();

        $this->getOptionalListenerManager()->enableListener(self::AUDIT_LISTENER);

        try {
            $this->doExecuteCommand($this->getCommandName(), ['--force' => true]);

            /**
             * It may be tempting to also assert the number of audit messages, but it would be wrong:
             *  - In older versions the message is sent and the consumer drops the fields when it reads it.
             *    So more customers mean more messages - one per 100 changed entities -
             *    {@see \Oro\Bundle\DataAuditBundle\EventListener\SendChangedEntitiesToMessageQueueListener::BATCH_SIZE}
             *  - And since 6.1 the listener drops non-auditable fields before sending, and message count is different.
             */

            self::consumeAllMessages();

            $addedAudits = $this->describeAuditsAddedAfter($lastAuditId);

            self::assertEquals(
                $auditFieldCount,
                $manager->getRepository(AuditField::class)->count([]),
                $addedAudits
            );
            self::assertEquals($auditCount, $manager->getRepository(Audit::class)->count([]), $addedAudits);
        } finally {
            // Leaving the listener on would make every following test in the process audit its own changes.
            $this->getOptionalListenerManager()->disableListener(self::AUDIT_LISTENER);
        }
    }

    abstract protected function getCommandName(): string;

    /**
     * Fixtures and the test itself produce messages, and consuming them is not enough to leave the queue empty:
     * {@see MessageQueueExtension::consumeAllMessages()} stops when the message collector runs empty,
     * while a round consumes the oldest messages of the whole queue rather than the ones this test sent,
     * and gives up when it runs out of its time budget.
     *
     * Anything left behind would be consumed below, with the data audit listener already switched on,
     * and the audit records of those unrelated changes would be counted as records the command produced -
     * which is what made this test fail at random.
     */
    protected function emptyMessageQueue(): void
    {
        self::consumeAllMessages();
        self::purgeMessageQueue();
        self::clearMessageCollector();
    }

    protected function getLastAuditId(): int
    {
        return (int) self::getDataFixturesExecutorEntityManager()
            ->getRepository(Audit::class)
            ->createQueryBuilder('audit')
            ->select('COALESCE(MAX(audit.id), 0)')
            ->getQuery()
            ->getSingleScalarResult();
    }

    /**
     * Names what was audited, so that a failure points at the entity and the field
     * instead of only saying how many audit records there are now.
     */
    protected function describeAuditsAddedAfter(int $lastAuditId): string
    {
        $rows = self::getDataFixturesExecutorEntityManager()
            ->getRepository(Audit::class)
            ->createQueryBuilder('audit')
            ->select(
                'audit.action AS action',
                'audit.objectClass AS objectClass',
                'auditField.field AS fieldName',
                'COUNT(audit.id) AS recordCount'
            )
            ->leftJoin('audit.fields', 'auditField')
            ->where('audit.id > :lastAuditId')
            ->setParameter('lastAuditId', $lastAuditId)
            ->groupBy('audit.action', 'audit.objectClass', 'auditField.field')
            ->orderBy('recordCount', 'DESC')
            ->setMaxResults(20)
            ->getQuery()
            ->getArrayResult();

        if (!$rows) {
            return 'No audit record was added after the command.';
        }

        $lines = [];
        foreach ($rows as $row) {
            $lines[] = \sprintf(
                '  %s %s::%s: %d record(s)',
                $row['action'],
                $row['objectClass'],
                $row['fieldName'] ?? '(no field)',
                $row['recordCount']
            );
        }

        return 'Audit records added while the command was running:' . PHP_EOL . \implode(PHP_EOL, $lines);
    }
}
