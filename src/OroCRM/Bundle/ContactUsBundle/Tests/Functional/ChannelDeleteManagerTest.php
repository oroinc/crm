<?php

namespace OroCRM\Bundle\ContactUsBundle\Tests\Functional;

use Oro\Bundle\IntegrationBundle\Entity\Channel;
use Oro\Bundle\IntegrationBundle\Tests\Functional\AbstractChannelDataDeleteTest;

use OroCRM\Bundle\ContactUsBundle\Entity\ContactRequest;

/**
 * @outputBuffering enabled
 * @dbIsolation
 */
class ChannelDeleteManagerTest extends AbstractChannelDataDeleteTest
{
    protected function setUp()
    {
        parent::setUp();
        $this->entityClassName = 'OroCRMContactUsBundle:ContactRequest';
    }

    /**
     * {@inheritdoc}
     */
    protected function createRelatedEntity(Channel $channel)
    {
        $contactRequest = new ContactRequest();
        $contactRequest->setChannel($channel);
        $contactRequest->setFirstName('test');
        $contactRequest->setLastName('test');
        $contactRequest->setComment('test');
        $this->em->persist($contactRequest);
    }
}
