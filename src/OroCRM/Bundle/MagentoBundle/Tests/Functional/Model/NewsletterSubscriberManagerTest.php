<?php

namespace OroCRM\Bundle\MagentoBundle\Tests\Functional\Model;

use Oro\Bundle\TestFrameworkBundle\Test\WebTestCase;

use OroCRM\Bundle\MagentoBundle\Entity\Customer;
use OroCRM\Bundle\MagentoBundle\Entity\NewsletterSubscriber;

/**
 * @dbIsolation
 */
class NewsletterSubscriberManagerTest extends WebTestCase
{
    protected function setUp()
    {
        $this->initClient([], $this->generateBasicAuthHeader());
        $this->loadFixtures(['OroCRM\Bundle\MagentoBundle\Tests\Functional\Fixture\LoadMagentoChannel']);
    }

    public function testCreateFromCustomer()
    {
        /** @var Customer $customer */
        $customer = $this->getReference('customer');

        $this->assertEmpty($customer->getNewsletterSubscriber());

        $newsletterSubscriber = $this->getContainer()->get('orocrm_magento.model.newsletter_subscriber_manager')
            ->getOrCreateFromCustomer($customer);

        $this->assertEquals($customer->getEmail(), $newsletterSubscriber->getEmail());
        $this->assertEquals($customer, $newsletterSubscriber->getCustomer());
        $this->assertEquals($customer->getChannel(), $newsletterSubscriber->getChannel());
        $this->assertEquals($customer->getStore(), $newsletterSubscriber->getStore());
        $this->assertEquals($customer->getOrganization(), $newsletterSubscriber->getOrganization());
        $this->assertEquals($customer->getOwner(), $newsletterSubscriber->getOwner());
        $this->assertEquals($customer->getDataChannel(), $newsletterSubscriber->getDataChannel());
    }

    public function testGetFromCustomer()
    {
        /** @var Customer $customer */
        $customer = $this->getReference('customer');

        $newsletterSubscriberBase = $this->getContainer()->get('doctrine')
            ->getRepository('OroCRMMagentoBundle:NewsletterSubscriber')
            ->findOneBy(['customer' => $customer->getId()]);
        $this->getContainer()->get('doctrine')->getManager()->refresh($customer);

        $newsletterSubscriber = $this->getContainer()->get('orocrm_magento.model.newsletter_subscriber_manager')
            ->getOrCreateFromCustomer($customer, NewsletterSubscriber::STATUS_UNSUBSCRIBED);

        $this->assertEquals($customer, $newsletterSubscriber->getCustomer());
        $this->assertEquals($newsletterSubscriberBase, $newsletterSubscriber);

        $this->getContainer()->get('doctrine')->getManager()->refresh($newsletterSubscriberBase);

        $this->assertEquals(NewsletterSubscriber::STATUS_UNSUBSCRIBED, $newsletterSubscriberBase->getStatus()->getId());
    }

    public function testChangeStatus()
    {
        $newsletterSubscriber = $this->getContainer()->get('doctrine')
            ->getRepository('OroCRMMagentoBundle:NewsletterSubscriber')
            ->findOneBy([]);

        $this->getContainer()->get('orocrm_magento.model.newsletter_subscriber_manager')
            ->changeStatus($newsletterSubscriber, NewsletterSubscriber::STATUS_UNSUBSCRIBED);

        $this->getContainer()->get('doctrine')->getManager()->refresh($newsletterSubscriber);

        $this->assertEquals(NewsletterSubscriber::STATUS_UNSUBSCRIBED, $newsletterSubscriber->getStatus()->getId());

    }
}
