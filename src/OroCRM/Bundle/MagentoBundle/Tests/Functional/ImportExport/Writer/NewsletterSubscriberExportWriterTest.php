<?php

namespace OroCRM\Bundle\MagentoBundle\Tests\Functional\ImportExport\Writer;

use Akeneo\Bundle\BatchBundle\Job\BatchStatus;

use OroCRM\Bundle\MagentoBundle\Entity\NewsletterSubscriber;

/**
 * @dbIsolation
 */
class NewsletterSubscriberExportWriterTest extends AbstractExportWriterTest
{
    protected function setUp()
    {
        parent::setUp();

        $this->getContainer()->get('orocrm_magento.importexport.writer.newsletter_subscriber')
            ->setTransport($this->transport);

        $this->loadFixtures(['OroCRM\Bundle\MagentoBundle\Tests\Functional\Fixture\LoadNewsletterSubscriberData']);
    }

    public function testCreateNew()
    {
        $originId = time();

        // no failed jobs
        $this->assertEmpty($this->getJobs('magento_newsletter_subscriber_export', BatchStatus::FAILED));

        /** @var NewsletterSubscriber $customer */
        $newsletterSubscriber = $this->getReference('newsletter_subscriber');
        $newsletterSubscriber->setOriginId(null);

        $this->transport->expects($this->never())->method('updateNewsletterSubscriber');
        $this->transport->expects($this->once())
            ->method('createNewsletterSubscriber')
            ->with($this->isType('array'))
            ->will($this->returnValue($originId));

        $jobResult = $this->getContainer()->get('oro_importexport.job_executor')->executeJob(
            'export',
            'magento_newsletter_subscriber_export',
            [
                'channel' => $newsletterSubscriber->getChannel()->getId(),
                'entity' => $newsletterSubscriber,
                'writer_skip_clear' => true
            ]
        );

        $this->assertEmpty($jobResult->getFailureExceptions());
        $this->assertTrue($jobResult->isSuccessful());

        // no failed jobs
        $this->assertEmpty($this->getJobs('magento_newsletter_subscriber_export', BatchStatus::FAILED));
    }

    public function testUpdateExisting()
    {
        // no failed jobs
        $this->assertEmpty($this->getJobs('magento_newsletter_subscriber_export', BatchStatus::FAILED));

        /** @var NewsletterSubscriber $customer */
        $newsletterSubscriber = $this->getReference('newsletter_subscriber');

        $this->transport->expects($this->once())
            ->method('updateNewsletterSubscriber')
            ->will($this->returnValue(true));

        $this->transport->expects($this->never())->method('createNewsletterSubscriber');

        $jobResult = $this->getContainer()->get('oro_importexport.job_executor')->executeJob(
            'export',
            'magento_newsletter_subscriber_export',
            [
                'channel' => $newsletterSubscriber->getChannel()->getId(),
                'entity' => $newsletterSubscriber,
                'writer_skip_clear' => true
            ]
        );

        $this->assertEmpty($jobResult->getFailureExceptions());
        $this->assertTrue($jobResult->isSuccessful());

        // no failed jobs
        $this->assertEmpty($this->getJobs('magento_newsletter_subscriber_export', BatchStatus::FAILED));
    }
}
