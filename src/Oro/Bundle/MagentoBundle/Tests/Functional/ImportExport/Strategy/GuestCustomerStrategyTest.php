<?php

namespace Oro\Bundle\MagentoBundle\Tests\Functional\ImportExport\Strategy;

use Akeneo\Bundle\BatchBundle\Entity\JobExecution;
use Akeneo\Bundle\BatchBundle\Entity\JobInstance;
use Akeneo\Bundle\BatchBundle\Entity\StepExecution;
use Oro\Bundle\ImportExportBundle\Context\StepExecutionProxyContext;
use Oro\Bundle\MagentoBundle\Entity\Customer;
use Oro\Bundle\MagentoBundle\ImportExport\Strategy\GuestCustomerStrategy;
use Oro\Bundle\MagentoBundle\Tests\Functional\Fixtures\LoadGuestCustomerStrategyData;
use Oro\Bundle\TestFrameworkBundle\Test\WebTestCase;

/**
 * @dbIsolationPerTest
 */
class GuestCustomerStrategyTest extends WebTestCase
{
    const STRATEGY_SERVICE_NAME = 'oro_magento.import.strategy.guest_customer.add_or_update';
    const STRATEGY_HELPER_SERVICE_NAME = 'oro_magento.importexport.guest_customer_strategy_helper';

    /**
     * @var GuestCustomerStrategy
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

    /** {@inheritdoc} */
    protected function setUp(): void
    {
        $this->initClient();

        $this->loadFixtures([
            LoadGuestCustomerStrategyData::class
        ]);

        $this->strategy = $this->getContainer()->get(self::STRATEGY_SERVICE_NAME);
        $this->strategy->setEntityName(Customer::class);

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
     * {@inheritdoc}
     */
    protected function tearDown(): void
    {
        unset(
            $this->strategy,
            $this->context,
            $this->stepExecution
        );

        parent::tearDown();
    }

    /**
     * @dataProvider processDataProvider
     *
     * @param string[]|string   $customer
     * @param string[]|null     $expected
     */
    public function testProcess(array $customer, $expected)
    {
        $customer = $this->createCustomer(
            $customer['firstName'],
            $customer['lastName'],
            $customer['email']
        );

        $result = $this->strategy->process($customer);

        if (is_array($expected)) {
            $this->assertEquals($expected['firstName'], $result->getFirstName());
            $this->assertEquals($expected['lastName'], $result->getLastName());
            $this->assertEquals($expected['email'], $result->getEmail());
        } else {
            $this->assertEquals($expected, $result);
        }
    }

    public function testImportCustomersInOneBatch()
    {
        $customer = $this->createCustomer(
            'Chief',
            'Wiggum',
            LoadGuestCustomerStrategyData::TEST_SHARED_EMAIL
        );

        $this->strategy->process($customer);

        $sameCustomer = $this->createCustomer(
            'Chief',
            'Wiggum',
            LoadGuestCustomerStrategyData::TEST_SHARED_EMAIL
        );

        $sameCustomerEntity = $this->strategy->process($sameCustomer);

        $this->assertSame(
            $customer,
            $sameCustomerEntity
        );

        $customerWithNewFirstName = $this->createCustomer(
            'Samuel',
            'Wiggum',
            LoadGuestCustomerStrategyData::TEST_SHARED_EMAIL
        );

        $this->assertNotSame(
            $customer,
            $customerWithNewFirstName
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
                'expected' => [
                    'firstName' => 'Homer',
                    'lastName'  => 'Simpson',
                    'email'     => 'homerS@springfield.com'
                ],
            ],
            'Existing customer, none shared email' => [
                'customer' => [
                    'firstName' => 'John',
                    'lastName'  => 'Doe',
                    'email'     =>  LoadGuestCustomerStrategyData::NONE_SHARED_EMAIL
                ],
                'expected' => null
            ],
            'Existing customer, shared email' => [
                'customer' => [
                    'firstName' => 'John',
                    'lastName'  => 'Doe',
                    'email'     => LoadGuestCustomerStrategyData::TEST_SHARED_EMAIL
                ],
                'expected' => null
            ],
            'New customer, shared email' => [
                'customer' => [
                    'firstName' => 'Ned',
                    'lastName' => 'Flanders',
                    'email' => LoadGuestCustomerStrategyData::JOHN_DOE_SHARED_EMAIL,
                ],
                'expected' => [
                    'firstName' => 'Ned',
                    'lastName' => 'Flanders',
                    'email' => LoadGuestCustomerStrategyData::JOHN_DOE_SHARED_EMAIL,
                ]
            ]
        ];
    }

    /**
     * @param string $firstName
     * @param string $lastName
     * @param string $email
     *
     * @return Customer
     */
    protected function createCustomer($firstName, $lastName, $email)
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
}
