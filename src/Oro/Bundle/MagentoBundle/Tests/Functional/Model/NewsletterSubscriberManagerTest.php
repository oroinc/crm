<?php

namespace Oro\Bundle\MagentoBundle\Tests\Functional\Model;

use Oro\Bundle\IntegrationBundle\Entity\Channel;
use Oro\Bundle\MagentoBundle\Entity\Customer;
use Oro\Bundle\MagentoBundle\Entity\NewsletterSubscriber;
use Oro\Bundle\TestFrameworkBundle\Test\WebTestCase;

class NewsletterSubscriberManagerTest extends WebTestCase
{
    protected function setUp(): void
    {
        $this->initClient([], $this->generateBasicAuthHeader());
        $this->loadFixtures(['Oro\Bundle\MagentoBundle\Tests\Functional\Fixture\LoadNewsletterSubscriberData']);
    }

    public function testCreateFromCustomer()
    {
        /** @var Channel $integration */
        $integration = $this->getReference('integration');

        /** @var Customer $customer */
        $customer = new Customer();
        $customer->setChannel($integration);
        $this->assertEmpty($customer->getNewsletterSubscribers());

        $newsletterSubscribers = $this->getContainer()->get('oro_magento.model.newsletter_subscriber_manager')
            ->getOrCreateFromCustomer($customer);

        $this->assertNotEmpty($newsletterSubscribers);
        $newsletterSubscriber = $newsletterSubscribers[0];
        $this->assertEquals($customer->getEmail(), $newsletterSubscriber->getEmail());
        $this->assertEquals($customer, $newsletterSubscriber->getCustomer());
        $this->assertEquals($customer->getChannel(), $newsletterSubscriber->getChannel());
        $this->assertEquals($customer->getStore(), $newsletterSubscriber->getStore());
        $this->assertEquals($customer->getOrganization(), $newsletterSubscriber->getOrganization());
        $this->assertEquals($customer->getOwner(), $newsletterSubscriber->getOwner());
        $this->assertEquals($customer->getDataChannel(), $newsletterSubscriber->getDataChannel());

        $this->assertEquals(NewsletterSubscriber::STATUS_UNSUBSCRIBED, $newsletterSubscriber->getStatus()->getId());
    }

    public function testGetFromCustomer()
    {
        /** @var Customer $customer */
        $customer = $this->getReference('customer');
        $this->assertNotEmpty($customer->getNewsletterSubscribers());

        $newsletterSubscribers = $this->getContainer()->get('oro_magento.model.newsletter_subscriber_manager')
            ->getOrCreateFromCustomer($customer);

        $this->assertCount(2, $newsletterSubscribers);
        $newsletterSubscriber = $newsletterSubscribers[0];
        $this->assertEquals($customer, $newsletterSubscriber->getCustomer());
        $this->assertNotEmpty($newsletterSubscriber->getId());
    }
}
