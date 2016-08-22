<?php

namespace OroCRM\Bundle\SalesBundle\Tests\Functional\ImportExport\Strategy;

use Akeneo\Bundle\BatchBundle\Entity\JobExecution;
use Akeneo\Bundle\BatchBundle\Entity\StepExecution;

use Oro\Bundle\AddressBundle\Entity\Address;
use Oro\Bundle\AddressBundle\Entity\Country;
use Oro\Bundle\ImportExportBundle\Context\StepExecutionProxyContext;
use Oro\Bundle\TestFrameworkBundle\Test\WebTestCase;

use OroCRM\Bundle\AccountBundle\Entity\Account;
use OroCRM\Bundle\ChannelBundle\Entity\Channel;
use OroCRM\Bundle\SalesBundle\Entity\B2bCustomer;
use OroCRM\Bundle\SalesBundle\ImportExport\Strategy\B2bConfigurableAddOrReplaceStrategy;


/**
 * @dbIsolationPerTest
 */
class B2bConfigurableAddOrReplaceStrategyTest extends WebTestCase
{
    /**
     * @var B2bConfigurableAddOrReplaceStrategy
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

    protected function setUp()
    {
        $this->initClient(
            ['debug' => false],
            array_merge($this->generateBasicAuthHeader(), array('HTTP_X-CSRF-Header' => 1))
        );
        $this->client->useHashNavigation(true);

        $this->loadFixtures(
            [
                'OroCRM\Bundle\SalesBundle\Tests\Functional\Fixture\LoadSalesBundleFixtures'
            ]
        );

        $container = $this->getContainer();

        $this->strategy = new B2bConfigurableAddOrReplaceStrategy(
            $container->get('event_dispatcher'),
            $container->get('oro_importexport.strategy.import.helper'),
            $container->get('oro_importexport.field.field_helper'),
            $container->get('oro_importexport.field.database_helper'),
            $container->get('oro_entity.entity_class_name_provider'),
            $container->get('translator'),
            $container->get('oro_importexport.strategy.new_entities_helper'),
            $container->get('oro_entity.doctrine_helper')
        );

        $this->stepExecution = new StepExecution('step', new JobExecution());
        $this->context = new StepExecutionProxyContext($this->stepExecution);
        $this->strategy->setImportExportContext($this->context);
        $this->strategy->setEntityName(
            $container->getParameter('orocrm_sales.b2bcustomer.entity.class')
        );
    }

    protected function tearDown()
    {
        unset($this->strategy, $this->context, $this->stepExecution);
    }

    public function testUpdateAddress()
    {
        $address = new Address();
        $address->setStreet('Test1');
        $address->setCity('test_city');
        $country = new Country('US');
        $address->setCountry($country);

        $account = new Account();
        $account->setName('some account name');

        $channel = new Channel();
        $channel->setName('b2b Channel');

        $newB2bCustomer = new B2bCustomer();
        $newB2bCustomer->setName('b2bCustomer name');
        $newB2bCustomer->setShippingAddress($address);
        $newB2bCustomer->setBillingAddress($address);
        $newB2bCustomer->setAccount($account);
        $newB2bCustomer->setDataChannel($channel);

        /** @var B2bCustomer $existedCustomer */
        $existedCustomer = $this->getReference('default_b2bcustomer');
        self::assertEquals('Test street', $existedCustomer->getShippingAddress()->getStreet());
        $this->strategy->process($newB2bCustomer);
        self::assertEquals('Test1', $existedCustomer->getShippingAddress()->getStreet());
    }

    public function testUpdateCustomerByEmptyAddress()
    {
        $account = new Account();
        $account->setName('some account name');

        $channel = new Channel();
        $channel->setName('b2b Channel');

        $newB2bCustomer = new B2bCustomer();
        $newB2bCustomer->setName('b2bCustomer name');
        $newB2bCustomer->setAccount($account);
        $newB2bCustomer->setDataChannel($channel);

        /** @var B2bCustomer $existedCustomer */
        $existedCustomer = $this->getReference('default_b2bcustomer');
        self::assertEquals('Test street', $existedCustomer->getShippingAddress()->getStreet());
        $this->strategy->process($newB2bCustomer);
        self::assertNull($existedCustomer->getShippingAddress());
    }
}
