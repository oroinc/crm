<?php

namespace OroCRM\Bundle\MagentoBundle\Tests\Unit\Importexport\Strategy;

use Symfony\Component\PropertyAccess\PropertyAccess;

use Oro\Bundle\ImportExportBundle\Context\ContextInterface;
use Oro\Bundle\IntegrationBundle\Entity\Channel;

use OroCRM\Bundle\MagentoBundle\Entity\Cart;
use OroCRM\Bundle\MagentoBundle\Entity\CartStatus;
use OroCRM\Bundle\MagentoBundle\Entity\Customer;
use OroCRM\Bundle\MagentoBundle\ImportExport\Strategy\CartStrategy;

class CartStrategyTest extends AbstractStrategyTest
{
    /**
     * {@inheritdoc}
     */
    protected function getStrategy()
    {
        $strategy = new CartStrategy(
            $this->eventDispatcher,
            $this->strategyHelper,
            $this->fieldHelper,
            $this->databaseHelper
        );

        $strategy->setOwnerHelper($this->defaultOwnerHelper);
        $strategy->setLogger($this->logger);
        $strategy->setChannelHelper($this->channelHelper);

        return $strategy;
    }

    /**
     * @param mixed $expected
     * @param mixed $entity
     * @param mixed $databaseEntity
     *
     * @dataProvider contactInfoDataProvider
     */
    public function testContactInfo($expected, $entity, $databaseEntity = null)
    {
        $strategy = $this->getStrategy();

        /** @var \PHPUnit_Framework_MockObject_MockObject|ContextInterface $context */
        $context = $this->getMock('Oro\Bundle\ImportExportBundle\Context\ContextInterface');
        $strategy->setImportExportContext($context);
        $strategy->setEntityName('OroCRM\Bundle\MagentoBundle\Entity\Cart');

        $execution = $this->getMock('Akeneo\Bundle\BatchBundle\Item\ExecutionContext');
        $this->jobExecution->expects($this->any())->method('getExecutionContext')
            ->will($this->returnValue($execution));
        $strategy->setStepExecution($this->stepExecution);

        $this->databaseHelper->expects($this->once())->method('getEntityReference')->will(
            $this->returnCallback(
                function ($item) {
                    return $item;
                }
            )
        );

        $this->databaseHelper->expects($this->once())->method('findOneByIdentity')->willReturn($databaseEntity);

        $this->assertEquals($expected, $strategy->process($entity));
    }

    /**
     * @return array
     */
    public function contactInfoDataProvider()
    {
        return [
            'items count' => [null, $this->getEntity()],
            'without contact info' => [null, $this->getEntity(['itemsCount' => 1])],
            'email' => [
                $this->getEntity(['itemsCount' => 1, 'email' => 'user@example.com']),
                $this->getEntity(['itemsCount' => 1, 'email' => 'user@example.com'])
            ],
            'dont change status' => [
                $this->getEntity(
                    ['itemsCount' => 1, 'email' => 'user@example.com', 'status' => new CartStatus('custom')]
                ),
                $this->getEntity(
                    ['itemsCount' => 1, 'email' => 'user@example.com', 'status' => new CartStatus('custom')]
                ),
                $this->getEntity(
                    ['itemsCount' => 1, 'email' => 'user@example.com', 'status' => new CartStatus('custom')]
                )
            ],
            'change status' => [
                $this->getEntity(
                    ['itemsCount' => 1, 'email' => 'user@example.com', 'status' => new CartStatus('expired')]
                ),
                $this->getEntity(
                    ['itemsCount' => 1, 'email' => 'user@example.com', 'status' => new CartStatus('expired')]
                ),
                $this->getEntity(
                    ['itemsCount' => 1, 'email' => 'user@example.com', 'status' => new CartStatus('open')]
                )
            ],
            'update customer email' => [
                $this->getEntity(
                    [
                        'itemsCount' => 1,
                        'email' => 'user@example.com',
                        'customer' => $this->getCustomer('user@example.com')
                    ]
                ),
                $this->getEntity(
                    [
                        'itemsCount' => 1,
                        'email' => 'user@example.com',
                        'customer' => $this->getCustomer('customer@example.com')
                    ]
                ),
                $this->getEntity(
                    [
                        'itemsCount' => 1,
                        'email' => 'user@example.com',
                        'customer' => $this->getCustomer('database@example.com')
                    ]
                )
            ]
        ];
    }

    /**
     * @param string $email
     * @return Customer
     */
    protected function getCustomer($email)
    {
        $customer = new Customer();
        $customer->setEmail($email);

        return $customer;
    }

    /**
     * @param array $properties
     *
     * @return Cart
     */
    protected function getEntity(array $properties = [])
    {
        $cart = new Cart();

        $channel = new Channel();
        $cart->setChannel($channel);

        $propertyAccessor = PropertyAccess::createPropertyAccessor();

        foreach ($properties as $property => $value) {
            $propertyAccessor->setValue($cart, $property, $value);
        }

        return $cart;
    }
}
