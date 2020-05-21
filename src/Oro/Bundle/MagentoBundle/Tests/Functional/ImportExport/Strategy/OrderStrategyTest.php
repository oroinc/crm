<?php

namespace Oro\Bundle\MagentoBundle\Tests\Functional\ImportExport\Strategy;

use Akeneo\Bundle\BatchBundle\Entity\JobExecution;
use Akeneo\Bundle\BatchBundle\Entity\JobInstance;
use Akeneo\Bundle\BatchBundle\Entity\StepExecution;
use Oro\Bundle\AddressBundle\Entity\Country;
use Oro\Bundle\ImportExportBundle\Context\StepExecutionProxyContext;
use Oro\Bundle\MagentoBundle\Entity\Order;
use Oro\Bundle\MagentoBundle\Entity\OrderAddress;
use Oro\Bundle\MagentoBundle\Entity\OrderNote;
use Oro\Bundle\MagentoBundle\ImportExport\Strategy\OrderStrategy;
use Oro\Bundle\MagentoBundle\Tests\Functional\Fixture\LoadMagentoChannel;
use Oro\Bundle\TestFrameworkBundle\Test\WebTestCase;
use Oro\Component\Testing\Unit\EntityTrait;

/**
 * @dbIsolationPerTest
 */
class OrderStrategyTest extends WebTestCase
{
    use EntityTrait;

    /**
     * @var OrderStrategy
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
            LoadMagentoChannel::class
        ]);

        $this->strategy = $this->getContainer()->get('oro_magento.import.strategy.order.add_or_update');
        $this->strategy->setEntityName(Order::class);

        $jobInstance = new JobInstance();
        $jobInstance->setRawConfiguration(['channel' => 3]);
        $jobExecution = new JobExecution();
        $jobExecution->setJobInstance($jobInstance);
        $this->stepExecution = new StepExecution('step', $jobExecution);
        $this->context = new StepExecutionProxyContext($this->stepExecution);
        $this->strategy->setImportExportContext($this->context);
        $this->strategy->setStepExecution($this->stepExecution);
    }

    public function testProcessAddressCountryTextSetTuNullWhenCountryIsSet()
    {
        $em = $this->getContainer()->get('doctrine')->getManagerForClass(Order::class);
        $order = $em->getRepository(Order::class)->findOneBy([]);

        $street = 'Test_Street_001';
        $country = new Country('US');
        $address = new OrderAddress();
        $address->setCountry($country);
        $address->setCountryText('Test');
        $address->setStreet($street);

        $order->resetAddresses([$address]);

        /** @var Order $processedOrder */
        $processedOrder = $this->strategy->process($order);
        /** @var OrderAddress $processedAddress */
        $processedAddress = $processedOrder->getAddresses()->first();
        $this->assertInstanceOf(OrderAddress::class, $processedAddress);
        $this->assertSame($street, $processedAddress->getStreet());
        $this->assertInstanceOf(Country::class, $processedAddress->getCountry());
        $this->assertNull($processedAddress->getCountryText());
    }

    public function testProcessAddressCountryTextSetTuNullWhenCountryIsEmpty()
    {
        $em = $this->getContainer()->get('doctrine')->getManagerForClass(Order::class);
        $order = $em->getRepository(Order::class)->findOneBy([]);

        $street = 'Test_Street_002';
        $address = new OrderAddress();
        $address->setCountryText('Test');
        $address->setStreet($street);

        $order->resetAddresses([$address]);

        /** @var Order $processedOrder */
        $processedOrder = $this->strategy->process($order);
        /** @var OrderAddress $processedAddress */
        $processedAddress = $processedOrder->getAddresses()->first();
        $this->assertInstanceOf(OrderAddress::class, $processedAddress);
        $this->assertSame($street, $processedAddress->getStreet());
        $this->assertNull($processedAddress->getCountry());
        $this->assertSame('Test', $processedAddress->getCountryText());
    }

    /**
     * @dataProvider orderNoteDataProvider
     *
     * @param mixed[]     $orderNotesData
     * @param int         $expectedCountOrderNotes
     * @param int[]       $expectedOrderOriginIds
     */
    public function testProcessValidOrderNote(array $orderNotesData, $expectedCountOrderNotes, $expectedOrderOriginIds)
    {
        $em = $this->getContainer()->get('doctrine')->getManagerForClass(Order::class);
        /** @var Order $order */
        $order = $em->getRepository(Order::class)->findOneBy([]);

        /**
         * @var $orderNote OrderNote
         */
        foreach ($orderNotesData as $orderNoteData) {
            $orderNote = $this->getEntity(OrderNote::class, $orderNoteData);
            $order->addOrderNote($orderNote);
            unset($orderNote);
        }

        $this->strategy->process($order);
        $orderNotesCollection = $order->getOrderNotes();

        $this->assertInstanceOf(Order::class, $order);
        $this->assertCount(
            $expectedCountOrderNotes,
            $orderNotesCollection
        );

        $actualOrderNoteIds = $orderNotesCollection->map(function (OrderNote $orderNote) {
            return $orderNote->getOriginId();
        })->toArray();

        $this->assertEquals(
            $expectedOrderOriginIds,
            $actualOrderNoteIds
        );
    }

    /**
     * @return mixed[]
     */
    public function orderNoteDataProvider()
    {
        return [
            'test invalid order note' => [
                'orderNotes' => [
                    [
                        'originId' => null,
                        'message'  => null
                    ]
                ],
                'expectedCountOrderNotes' => 0,
                'expectedOrderOriginIds' => []
            ],
            'test valid order note' => [
                'orderNotes' => [
                    [
                        'originId' => 2000001,
                        'message'  => 'test message'
                    ]
                ],
                'expectedCountOrderNotes' => 1,
                'expectedOrderOriginIds' => [
                    2000001
                ]
            ],
            'test valid order notes' => [
                'orderNotes' => [
                    [
                        'originId' => 2000001,
                        'message'  => 'test message'
                    ],
                    [
                        'originId' => 2000002,
                        'message'  => 'test message 2'
                    ]
                ],
                'expectedCountOrderNotes' => 2,
                'expectedOrderOriginIds' => [
                    2000001,
                    2000002
                ]
            ],
            'test valid order note with invalid order note without message' => [
                'orderNotes' => [
                    [
                        'originId' => 2000001,
                        'message'  => 'test message'
                    ],
                    [
                        'originId' => 2000002,
                        'message'  => null
                    ]
                ],
                'expectedCountOrderNotes' => 1,
                'expectedOrderOriginIds' => [
                    2000001
                ]
            ],
            'test invalid order note without message' => [
                'orderNotes' => [
                    [
                        'originId' => 2000002,
                        'message'  => null
                    ]
                ],
                'expectedCountOrderNotes' => 0,
                'expectedOrderOriginIds' => []
            ],
            'test invalid order note without origin_id' => [
                'orderNotes' => [
                    [
                        'originId' => null,
                        'message'  => 'test message'
                    ]
                ],
                'expectedCountOrderNotes' => 0,
                'expectedOrderOriginIds' => []
            ]
        ];
    }

    public function testProcessOrderNoteMapping()
    {
        $messageWithMisc = 'message with js code<script type="text/javascript">alert("You are hacked !")</script>';
        $messageSanitized = 'message with js code';

        /**
         * @var $orderNote OrderNote
         */
        $orderNote = $this->getEntity(
            OrderNote::class,
            [
                'originId' => 1000001,
                'message'  => $messageWithMisc
            ]
        );

        /**
         * @var $order Order
         */
        $order = $this->getReference(LoadMagentoChannel::ORDER_ALIAS_REFERENCE_NAME);
        $order->addOrderNote($orderNote);


        $this->strategy->process($order);

        $this->assertEquals(
            $messageSanitized,
            $orderNote->getMessage(),
            'Incorrect message, check that message correctly sanitized !'
        );

        $this->assertSame(
            $this->getReference(LoadMagentoChannel::USER_ALIAS_REFERENCE_NAME),
            $orderNote->getOwner(),
            'Owner missed, check that mapping is correct !'
        );

        $this->assertSame(
            $this->getReference(LoadMagentoChannel::ORGANIZATION_ALIAS_REFERENCE_NAME),
            $orderNote->getOrganization(),
            'Organization missed, check that mapping is correct !'
        );
    }
}
