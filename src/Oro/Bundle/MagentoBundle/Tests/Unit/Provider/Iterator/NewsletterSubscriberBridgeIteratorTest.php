<?php

namespace Oro\Bundle\MagentoBundle\Tests\Unit\Provider\Iterator;

use Oro\Bundle\MagentoBundle\Provider\Iterator\Soap\NewsletterSubscriberBridgeIterator;
use Oro\Bundle\MagentoBundle\Provider\Iterator\UpdatedLoaderInterface;

class NewsletterSubscriberBridgeIteratorTest extends BaseSoapIteratorTestCase
{
    /**
     * @var NewsletterSubscriberBridgeIterator
     */
    protected $iterator;

    protected function setUp(): void
    {
        parent::setUp();

        $this->iterator = new NewsletterSubscriberBridgeIterator($this->transport, $this->settings);
    }

    /**
     * @param array $data
     * @param array $stores
     *
     * @dataProvider dataProvider
     */
    public function testIteration(array $data, array $stores)
    {
        $this->transport->expects($this->atLeastOnce())->method('call')
            ->with($this->equalTo('newsletterSubscriberList'))
            ->will($this->returnValue($data));

        $this->assertEquals(
            [
                1 => (array)$data[0],
                2 => (array)$data[1],
                3 => (array)$data[2]
            ],
            iterator_to_array($this->iterator)
        );
    }

    /**
     * @param array $data
     * @param array $stores
     *
     * @dataProvider dataProvider
     */
    public function testIterationWithInitialId(array $data, array $stores)
    {
        $this->iterator->setInitialId(time());

        $this->transport->expects($this->once())->method('call')
            ->with($this->equalTo('newsletterSubscriberList'))
            ->will($this->returnValue($data));

        $this->assertEquals(
            [
                1 => (array)$data[0],
                2 => (array)$data[1],
                3 => (array)$data[2]
            ],
            iterator_to_array($this->iterator)
        );
    }

    /**
     * @param array $data
     * @param array $stores
     *
     * @dataProvider dataProvider
     */
    public function testInitialMode(array $data, array $stores)
    {
        $this->iterator->setMode(UpdatedLoaderInterface::IMPORT_MODE_INITIAL);
        $this->testIteration($data, $stores);
    }

    /**
     * @param array $data
     * @param array $stores
     *
     * @dataProvider dataProvider
     */
    public function testInitialModeWithInitialId(array $data, array $stores)
    {
        $this->iterator->setMode(UpdatedLoaderInterface::IMPORT_MODE_INITIAL);
        $this->testIterationWithInitialId($data, $stores);
    }

    /**
     * @return array
     */
    public function dataProvider()
    {
        return [
            'one test case' => [
                [
                    (object)[
                        'subscriber_id' => 1,
                        'change_status_at' => (array)new \DateTime(),
                        'customer_id' => 2,
                        'store_id' => 1,
                        'subscriber_email' => 'email1@example.com',
                        'subscriber_status' => 1,
                        'subscriber_confirm_code' => uniqid()
                    ],
                    (object)[
                        'subscriber_id' => 2,
                        'change_status_at' => (array)new \DateTime(),
                        'customer_id' => 3,
                        'store_id' => 1,
                        'subscriber_email' => 'email2@example.com',
                        'subscriber_status' => 2,
                        'subscriber_confirm_code' => uniqid()
                    ],
                    (object)[
                        'subscriber_id' => 3,
                        'change_status_at' => (array)new \DateTime(),
                        'customer_id' => 4,
                        'store_id' => 1,
                        'subscriber_email' => 'email3@example.com',
                        'subscriber_status' => 3,
                        'subscriber_confirm_code' => uniqid()
                    ]
                ],
                [
                    1 => [
                        'id' => 1,
                        'code' => 'admin',
                        'name' => 'Admin'
                    ]
                ]
            ]
        ];
    }
}
