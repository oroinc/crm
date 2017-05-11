<?php

namespace Oro\Bundle\MagentoBundle\Tests\Functional\EventListener;

use Symfony\Bridge\Doctrine\RegistryInterface;

use Oro\Bundle\AccountBundle\Entity\Account;
use Oro\Bundle\ContactBundle\Entity\Contact;
use Oro\Bundle\MagentoBundle\Tests\Functional\Fixture\LoadCustomerData;
use Oro\Bundle\MagentoBundle\Tests\Functional\Fixture\LoadCustomerContact;
use Oro\Bundle\MagentoBundle\Entity\Customer;
use Oro\Bundle\MessageQueueBundle\Test\Functional\MessageQueueAssertTrait;
use Oro\Bundle\SearchBundle\Async\Topics;
use Oro\Bundle\TestFrameworkBundle\Test\WebTestCase;

/**
 * @group search
 * @dbIsolationPerTest
 */
class SearchIndexListenerTest extends WebTestCase
{
    use MessageQueueAssertTrait;

    /** @var RegistryInterface */
    private $registry;

    /**
     * {@inheritdoc}
     */
    protected function setUp()
    {
        parent::setUp();

        $this->initClient();

        $this->loadFixtures([
            LoadCustomerData::class,
            LoadCustomerContact::class,
        ]);

        $optionalListenersManager = $this->getContainer()->get('oro_platform.optional_listeners.manager');
        $optionalListenersManager->disableListener('oro_search.index_listener');
        $optionalListenersManager->enableListener('oro_magento.event_listener.delayed_search_reindex');

        $this->registry = $this->getContainer()->get('doctrine');
    }

    /**
     * {@inheritdoc}
     */
    protected function tearDown()
    {
        parent::tearDown();

        unset($this->registry);
    }

    public function testShouldCreateSearchIndexForEntityWithDependencies()
    {
        $em = $this->registry->getManagerForClass(Customer::class);
        self::getMessageCollector()->clear();

        $customer = new Customer();
        $customer->setContact($this->getReference('contact'));
        $customer->setAccount($this->getReference('account'));
        $customer->setCreatedAt(new \DateTime());
        $customer->setUpdatedAt(new \DateTime());

        $em->persist($customer);
        $em->flush();

        self::assertMessageSent(Topics::INDEX_ENTITIES, [
            ['class' => Contact::class, 'id' => $customer->getContact()->getId()],
            ['class' => Account::class, 'id' => $customer->getAccount()->getId()],
            ['class' => Customer::class, 'id' => $customer->getId()],
        ]);
    }

    public function testShouldUpdateSearchIndexForEntityWithDependencies()
    {
        $em = $this->registry->getManagerForClass(Customer::class);
        self::getMessageCollector()->clear();

        /** @var Customer $customer */
        $customer = $this->getReference('customer_default');
        $customer->setFirstName('test');
        $customer->setContact($this->getReference('contact'));

        $em->persist($customer);
        $em->flush();

        self::assertMessageSent(Topics::INDEX_ENTITIES, [
            ['class' => Customer::class, 'id' => $customer->getId()],
            ['class' => Contact::class, 'id' => $customer->getContact()->getId()],
            ['class' => Account::class, 'id' => $customer->getAccount()->getId()],
        ]);
    }

    public function testShouldDeleteSearchIndexForEntityWithDependencies()
    {
        $em = $this->registry->getManagerForClass(Customer::class);
        self::getMessageCollector()->clear();

        /** @var Customer $customer */
        $customer = $this->getReference('customer_default');
        $customerId = $customer->getId();

        $em->remove($customer);
        $em->flush();

        self::assertMessageSent(Topics::INDEX_ENTITIES, [
            ['class' => Customer::class, 'id' => $customerId]
        ]);
    }
}
