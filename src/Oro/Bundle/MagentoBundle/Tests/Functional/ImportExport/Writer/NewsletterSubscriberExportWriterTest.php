<?php

namespace Oro\Bundle\MagentoBundle\Tests\Functional\ImportExport\Writer;

use Akeneo\Bundle\BatchBundle\Job\BatchStatus;
use Oro\Bundle\MagentoBundle\Entity\NewsletterSubscriber;
use Oro\Bundle\MagentoBundle\Entity\Store;

class NewsletterSubscriberExportWriterTest extends AbstractExportWriterTest
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->getContainer()->get('oro_magento.importexport.writer.newsletter_subscriber')
            ->setTransport($this->transport);

        $this->loadFixtures(['Oro\Bundle\MagentoBundle\Tests\Functional\Fixture\LoadNewsletterSubscriberData']);
    }

    public function testUpdateExisting()
    {
        // no failed jobs
        $this->assertEmpty($this->getJobs('magento_newsletter_subscriber_export', BatchStatus::FAILED));

        /** @var NewsletterSubscriber $newsletterSubscriber */
        $newsletterSubscriber = $this->getReference('newsletter_subscriber');

        /** @var Store $store */
        $store = $this->getReference('store');

        $this->transport->expects($this->once())
            ->method('updateNewsletterSubscriber')
            ->willReturn(
                [
                    'subscriber_status' => NewsletterSubscriber::STATUS_UNSUBSCRIBED,
                    'subscriber_id' => $newsletterSubscriber->getOriginId(),
                    'store_id' => $store->getOriginId()
                ]
            );
        $this->transport->expects($this->never())->method('createNewsletterSubscriber');

        $jobResult = $this->getContainer()->get('oro_importexport.job_executor')->executeJob(
            'export',
            'magento_newsletter_subscriber_export',
            [
                'channel' => $newsletterSubscriber->getChannel()->getId(),
                'entity' => $newsletterSubscriber,
                'writer_skip_clear' => true,
                'statusIdentifier' => NewsletterSubscriber::STATUS_UNSUBSCRIBED,
                'processorAlias' => 'oro_magento'
            ]
        );

        $this->assertEquals([], $jobResult->getFailureExceptions());
        $this->assertTrue($jobResult->isSuccessful());

        $newsletterSubscriber = $this->getContainer()->get('doctrine')
            ->getRepository('OroMagentoBundle:NewsletterSubscriber')
            ->findOneBy(['originId' => $newsletterSubscriber->getOriginId()]);
        $this->assertEquals(NewsletterSubscriber::STATUS_UNSUBSCRIBED, $newsletterSubscriber->getStatus()->getId());

        // no failed jobs
        $this->assertEmpty($this->getJobs('magento_newsletter_subscriber_export', BatchStatus::FAILED));
    }

    /**
     * @depends testUpdateExisting
     */
    public function testCreateNew()
    {
        // no failed jobs
        $this->assertEmpty($this->getJobs('magento_newsletter_subscriber_export', BatchStatus::FAILED));

        /** @var NewsletterSubscriber $newsletterSubscriber */
        $newsletterSubscriber = $this->getReference('newsletter_subscriber');
        $newsletterSubscriber->setOriginId(null);

        /** @var Store $store */
        $store = $this->getReference('store');

        $originId = time();

        $this->transport->expects($this->never())->method('updateNewsletterSubscriber');
        $this->transport->expects($this->once())
            ->method('createNewsletterSubscriber')
            ->with($this->isType('array'))
            ->willReturn(
                [
                    'subscriber_status' => NewsletterSubscriber::STATUS_SUBSCRIBED,
                    'subscriber_id' => $originId,
                    'store_id' => $store->getOriginId(),
                    'email' => $newsletterSubscriber->getEmail()
                ]
            );

        $jobResult = $this->getContainer()->get('oro_importexport.job_executor')->executeJob(
            'export',
            'magento_newsletter_subscriber_export',
            [
                'channel' => $newsletterSubscriber->getChannel()->getId(),
                'entity' => $newsletterSubscriber,
                'writer_skip_clear' => true,
                'statusIdentifier' => NewsletterSubscriber::STATUS_SUBSCRIBED,
                'processorAlias' => 'oro_magento'
            ]
        );

        $this->assertEquals([], $jobResult->getFailureExceptions());
        $this->assertTrue($jobResult->isSuccessful());

        $newsletterSubscriber = $this->getContainer()->get('doctrine')
            ->getRepository('OroMagentoBundle:NewsletterSubscriber')
            ->findOneBy(['originId' => $originId]);
        $this->assertEquals(NewsletterSubscriber::STATUS_SUBSCRIBED, $newsletterSubscriber->getStatus()->getId());

        // no failed jobs
        $this->assertEmpty($this->getJobs('magento_newsletter_subscriber_export', BatchStatus::FAILED));
    }
}
