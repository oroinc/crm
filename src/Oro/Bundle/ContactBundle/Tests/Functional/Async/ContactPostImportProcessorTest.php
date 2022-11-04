<?php

namespace Oro\Bundle\ContactBundle\Tests\Functional\Async;

use Oro\Bundle\ContactBundle\Async\Topic\ActualizeContactEmailAssociationsTopic;
use Oro\Bundle\MessageQueueBundle\Test\Functional\MessageQueueExtension;
use Oro\Bundle\TestFrameworkBundle\Test\WebTestCase;
use Oro\Component\MessageQueue\Consumption\MessageProcessorInterface;

/**
 * @dbIsolationPerTest
 */
class ContactPostImportProcessorTest extends WebTestCase
{
    use MessageQueueExtension;

    protected function setUp(): void
    {
        parent::setUp();

        $this->initClient();
    }

    public function testProcess(): void
    {
        $sentMessage = self::sendMessage(ActualizeContactEmailAssociationsTopic::getName(), []);
        self::consumeMessage($sentMessage);

        self::assertProcessedMessageStatus(MessageProcessorInterface::ACK, $sentMessage);
        self::assertProcessedMessageProcessor(
            'oro_contact.async.contact_post_import_processor',
            $sentMessage
        );
    }
}
