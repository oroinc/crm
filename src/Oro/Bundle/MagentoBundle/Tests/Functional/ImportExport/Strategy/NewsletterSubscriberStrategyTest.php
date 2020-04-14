<?php

namespace Oro\Bundle\MagentoBundle\Tests\Functional\ImportExport\Strategy;

use Akeneo\Bundle\BatchBundle\Entity\JobExecution;
use Akeneo\Bundle\BatchBundle\Entity\JobInstance;
use Akeneo\Bundle\BatchBundle\Entity\StepExecution;
use Oro\Bundle\ImportExportBundle\Context\StepExecutionProxyContext;
use Oro\Bundle\MagentoBundle\Entity\NewsletterSubscriber;
use Oro\Bundle\MagentoBundle\ImportExport\Strategy\NewsletterSubscriberStrategy;
use Oro\Bundle\TestFrameworkBundle\Test\WebTestCase;

class NewsletterSubscriberStrategyTest extends WebTestCase
{
    /**
     * @var NewsletterSubscriberStrategy
     */
    protected $strategy;

    /**
     * @var StepExecutionProxyContext
     */
    protected $context;

    /**
     * @var StepExecution
     */
    protected $stepExecution;

    protected function setUp(): void
    {
        $this->initClient();

        $this->loadFixtures(['Oro\Bundle\MagentoBundle\Tests\Functional\Fixture\LoadNewsletterSubscriberData']);

        $this->strategy = $this->getContainer()
            ->get('oro_magento.import.strategy.newsletter_subscriber.add_or_update');

        $jobInstance = new JobInstance();
        $jobInstance->setRawConfiguration(['channel' => 3]);
        $jobExecution = new JobExecution();
        $jobExecution->setJobInstance($jobInstance);
        $this->stepExecution = new StepExecution('step', $jobExecution);
        $this->context = new StepExecutionProxyContext($this->stepExecution);
        $this->strategy->setImportExportContext($this->context);
        $this->strategy->setStepExecution($this->stepExecution);
    }

    public function testProcessSuccessful()
    {
        /** @var NewsletterSubscriber $newsletterSubscriber */
        $newsletterSubscriber = $this->getReference('newsletter_subscriber');
        $newsletterSubscriber->setDataChannel(null);

        $this->strategy->setEntityName(get_class($newsletterSubscriber));

        $this->assertEquals($newsletterSubscriber, $this->strategy->process($newsletterSubscriber));
        $this->assertEquals($newsletterSubscriber->getDataChannel(), $this->getReference('default_channel'));
    }

    public function testProcessChangeStatusAtEmpty()
    {
        $now = new \DateTime('now', new \DateTimeZone('UTC'));
        $createdAt = clone $now;
        $createdAt->modify('-2 days');
        $updateAt = clone $now;
        $updateAt->modify('-1 day');

        /** @var NewsletterSubscriber $newsletterSubscriber */
        $newsletterSubscriber = $this->getReference('newsletter_subscriber');
        $newsletterSubscriber
            ->setChangeStatusAt(null)
            ->setCreatedAt($createdAt)
            ->setUpdatedAt($updateAt);

        $this->strategy->setEntityName(get_class($newsletterSubscriber));

        $updatedNewsletterSubscriber = $this->strategy->process($newsletterSubscriber);
        $this->assertEquals($newsletterSubscriber, $updatedNewsletterSubscriber);
        $this->assertNotEquals($createdAt, $updatedNewsletterSubscriber->getCreatedAt());
        $this->assertNotEquals($updateAt, $updatedNewsletterSubscriber->getUpdatedAt());
    }

    public function testProcessChangeExisting()
    {
        $now = new \DateTime('now', new \DateTimeZone('UTC'));
        $createdAt = clone $now;
        $createdAt->modify('-2 days');
        $updateAt = clone $now;
        $updateAt->modify('-1 day');

        /** @var NewsletterSubscriber $newsletterSubscriber */
        $newsletterSubscriber = $this->getReference('newsletter_subscriber');
        $newsletterSubscriber
            ->setChangeStatusAt($now)
            ->setCreatedAt($createdAt)
            ->setUpdatedAt($updateAt);

        $this->strategy->setEntityName(get_class($newsletterSubscriber));

        $updatedNewsletterSubscriber = $this->strategy->process($newsletterSubscriber);
        $this->assertEquals($newsletterSubscriber, $updatedNewsletterSubscriber);
        $this->assertNotEquals($updateAt, $updatedNewsletterSubscriber->getUpdatedAt());
        $this->assertEquals($createdAt, $updatedNewsletterSubscriber->getCreatedAt());
    }

    /**
     * @depends testProcessChangeExisting
     */
    public function testProcessChangeNewEntity()
    {
        $now = new \DateTime('now', new \DateTimeZone('UTC'));
        $createdAt = clone $now;
        $createdAt->modify('-2 days');
        $updateAt = clone $now;
        $updateAt->modify('-1 day');

        /** @var NewsletterSubscriber $newsletterSubscriber */
        $newsletterSubscriber = $this->getReference('newsletter_subscriber');
        $newsletterSubscriber
            ->setChangeStatusAt($now)
            ->setCreatedAt($createdAt)
            ->setUpdatedAt($updateAt);

        $class = new \ReflectionClass($newsletterSubscriber);
        $prop = $class->getProperty('id');
        $prop->setAccessible(true);
        $prop->setValue($newsletterSubscriber, null);

        $this->strategy->setEntityName(get_class($newsletterSubscriber));

        $updatedNewsletterSubscriber = $this->strategy->process($newsletterSubscriber);
        $this->assertEquals($newsletterSubscriber, $updatedNewsletterSubscriber);
        $this->assertNotEquals($createdAt, $updatedNewsletterSubscriber->getCreatedAt());
        $this->assertNotEquals($updateAt, $updatedNewsletterSubscriber->getUpdatedAt());
    }
}
