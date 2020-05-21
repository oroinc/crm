<?php

namespace Oro\Bundle\MagentoBundle\Tests\Functional\ImportExport\Strategy;

use Akeneo\Bundle\BatchBundle\Entity\JobExecution;
use Akeneo\Bundle\BatchBundle\Entity\JobInstance;
use Akeneo\Bundle\BatchBundle\Entity\StepExecution;
use Oro\Bundle\ImportExportBundle\Context\StepExecutionProxyContext;
use Oro\Bundle\MagentoBundle\Entity\Cart;
use Oro\Bundle\MagentoBundle\Entity\Customer;
use Oro\Bundle\MagentoBundle\Entity\Order;
use Oro\Bundle\MagentoBundle\Entity\Store;
use Oro\Bundle\MagentoBundle\ImportExport\Strategy\OrderWithExistingCustomerStrategy;
use Oro\Bundle\MagentoBundle\Tests\Functional\Fixture\LoadMagentoChannel;
use Oro\Bundle\MagentoBundle\Tests\Functional\Fixtures\LoadGuestCustomerStrategyData;
use Oro\Bundle\TestFrameworkBundle\Test\WebTestCase;

class OrderWithExistingCustomerStrategyTest extends WebTestCase
{
    const STRATEGY_SERVICE = 'oro_magento.import.strategy.order_with_customer.add_or_update';

    /**
     * @var OrderWithExistingCustomerStrategy
     */
    private $strategy;

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

        $this->loadFixtures([
            LoadMagentoChannel::class,
            LoadGuestCustomerStrategyData::class
        ]);

        $this->strategy = $this->getContainer()->get(self::STRATEGY_SERVICE);
        $this->strategy->setEntityName(Order::class);

        $jobInstance = new JobInstance();
        $jobInstance->setRawConfiguration(['channel' => $this->getReference('integration')]);
        $jobExecution = new JobExecution();
        $jobExecution->setJobInstance($jobInstance);
        $this->stepExecution = new StepExecution('step', $jobExecution);
        $this->context = new StepExecutionProxyContext($this->stepExecution);

        $this->strategy->setImportExportContext($this->context);
        $this->strategy->setStepExecution($this->stepExecution);
    }

    /**
     * @dataProvider processDataProvider
     *
     * @param string[]          $customerData
     * @param \Closure|null     $expectedPostProcessGuestCustomersCallback
     * @param Customer|null     $expectedCustomerAlias
     */
    public function testProcess(
        array $customerData,
        \Closure $expectedPostProcessGuestCustomersCallback = null,
        $expectedCustomerAlias = null
    ) {
        /** @var Order $order */
        $order = $this->createGuestOrder(
            $customerData['firstName'],
            $customerData['lastName'],
            $customerData['email']
        );

        $this->context->setValue(
            'itemData',
            $this->getExpectedResultCallback(
                $customerData['firstName'],
                $customerData['lastName'],
                $customerData['email']
            )()
        );

        /**
         * @var Order $order
         */
        $order = $this->strategy->process($order);

        if ($expectedCustomerAlias !== null) {
            $this->assertSame(
                $this->getReference($expectedCustomerAlias)->getId(),
                $order->getCustomer()->getId()
            );
        }

        $expected = $expectedPostProcessGuestCustomersCallback === null ?
            $expectedPostProcessGuestCustomersCallback : [$expectedPostProcessGuestCustomersCallback()];

        $postProcessGuestCustomersData = $this->stepExecution
            ->getJobExecution()
            ->getExecutionContext()
            ->get('postProcessGuestCustomers');

        $this->assertEquals(
            $expected,
            $postProcessGuestCustomersData
        );
    }

    /**
     * @return array
     */
    public function processDataProvider()
    {
        return [
            'New customer, none shared email' => [
                'customer' => [
                    'firstName' => 'Homer',
                    'lastName'  => 'Simpson',
                    'email'     => 'homerS@springfield.com'
                ],
                'expectedPostProcessGuestCustomersCallback' => $this->getExpectedResultCallback(
                    'Homer',
                    'Simpson',
                    'homerS@springfield.com'
                ),
                'expectedCustomerAlias' => null
            ],
            'Existing customer, none shared email' => [
                'customer' => [
                    'firstName' => 'Homer',
                    'lastName'  => 'Simpson',
                    'email'     => LoadGuestCustomerStrategyData::NONE_SHARED_EMAIL
                ],
                'expectedPostProcessGuestCustomersCallback' => null,
                'expectedCustomerAlias' => LoadGuestCustomerStrategyData::CUSTOMER_ALIAS_REFERENCE_NAME
            ],
            'Existing customer, shared email' => [
                'customer' => [
                    'firstName' => 'John',
                    'lastName'  => 'Doe',
                    'email'     => LoadGuestCustomerStrategyData::TEST_SHARED_EMAIL
                ],
                'expectedPostProcessGuestCustomersCallback' => null,
                'expectedCustomerAlias' => LoadGuestCustomerStrategyData::CUSTOMER_2_ALIAS_REFERENCE_NAME
            ],
            'New customer, shared email' => [
                'customer' => [
                    'firstName' => 'Ned',
                    'lastName' => 'Flanders',
                    'email' => LoadGuestCustomerStrategyData::JOHN_DOE_SHARED_EMAIL,
                ],
                'expectedPostProcessGuestCustomersCallback' => $this->getExpectedResultCallback(
                    'Ned',
                    'Flanders',
                    LoadGuestCustomerStrategyData::JOHN_DOE_SHARED_EMAIL
                ),
                'expectedCustomerAlias' => null
            ]
        ];
    }

    /**
     * @param string $firstName
     * @param string $lastName
     * @param string $email
     *
     * @return \Closure
     */
    protected function getExpectedResultCallback($firstName, $lastName, $email)
    {
        return function () use ($firstName, $lastName, $email) {
            /**
             * @var $store Store
             */
            $store = $this->getReference(
                LoadMagentoChannel::STORE_ALIAS_REFERENCE_NAME
            );

            return [
                'customerEmail' => $email,
                'customer_firstname' => $firstName,
                'customer_lastname' => $lastName,
                'createdAt' => $this->getUpdateAndCreateAtDateTime(),
                'updatedAt' => $this->getUpdateAndCreateAtDateTime(),
                'store_id' => $store->getId(),
                'storeName' => $store->getName(),
            ];
        };
    }

    /**
     * @param string $firstName
     * @param string $lastName
     * @param string $email
     *
     * @return Customer
     */
    protected function createGuestCustomer($firstName, $lastName, $email)
    {
        $customer = new Customer();
        $customer->setFirstName($firstName);
        $customer->setLastName($lastName);
        $customer->setEmail($email);
        $customer->setChannel(
            $this->getReference(
                LoadGuestCustomerStrategyData::INTEGRATION_ALIAS_REFERENCE_NAME
            )
        );

        return $customer;
    }

    /**
     * @param string $firstName
     * @param string $lastName
     * @param string $email
     *
     * @return Order
     */
    protected function createGuestOrder($firstName, $lastName, $email)
    {
        $customer = $this->createGuestCustomer($firstName, $lastName, $email);

        $order = new Order();
        $order->setCustomer($customer);
        $order->setOriginId(1);
        $order->setIncrementId((string) rand(1, 1000));
        $order->setCustomerEmail($email);
        $order->setCart(new Cart());
        $order->setStatus('open');
        $order->setCreatedAt(new \DateTime('now', new \DateTimeZone('UTC')));
        $order->setUpdatedAt(new \DateTime('now', new \DateTimeZone('UTC')));
        $order->setStore(
            $this->getReference(
                LoadMagentoChannel::STORE_ALIAS_REFERENCE_NAME
            )
        );
        $order->setChannel(
            $this->getReference(
                LoadGuestCustomerStrategyData::INTEGRATION_ALIAS_REFERENCE_NAME
            )
        );
        $order->doPreUpdate();

        return $order;
    }

    /**
     * @return \DateTime
     */
    protected function getUpdateAndCreateAtDateTime()
    {
        return \DateTime::createFromFormat('m/d/Y hh:mm:ss', '1/10/2018 00:00:00');
    }
}
