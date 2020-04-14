<?php

namespace Oro\Bundle\MagentoBundle\Tests\Unit\ImportExport\Converter;

use Oro\Bundle\MagentoBundle\ImportExport\Converter\OrderDataConverter;

/**
 * @SuppressWarnings("PHPMD.ExcessiveMethodLength")
 */
class OrderDataConverterTest extends \PHPUnit\Framework\TestCase
{
    /** @var OrderDataConverter */
    protected $converter;

    /** @var string */
    protected $now;

    /** {@inheritdoc} */
    protected function setUp(): void
    {
        $this->converter = new OrderDataConverter();
    }

    /** {@inheritdoc} */
    protected function tearDown(): void
    {
        $this->converter = null;
    }

    /**
     * @dataProvider importDataProvider
     *
     * @param mixed[] $data
     * @param mixed[] $expected
     */
    public function testConvertToImportFormat(array $data, array $expected)
    {
        $result = $this->converter->convertToImportFormat($data);

        $this->assertEquals($expected, $result);
    }

    /**
     * @return mixed[]
     */
    public function importDataProvider()
    {
        $formattedDateTime = $this->getDateTimeNow();

        return [
            'test order data with single status_history' => [
                'data' => [
                    'increment_id' => 1,
                    'order_id' => 1000001,
                    'store_id' => 1,
                    'customer_id' => 2,
                    'is_virtual' => 0,
                    'customer_is_guest' => 1,
                    'gift_message' => 'test gift message',
                    'remote_ip' => '127.0.0.1',
                    'store_name' => 'test store name',
                    'total_paid' => 15,
                    'total_invoiced' => 15,
                    'total_refunded' => 0,
                    'total_canceled' => 0,
                    'quote_id' => 1000002,
                    'payment_method' => 'test',
                    'order_currency_code' => 'USD',
                    'subtotal' => 24,
                    'shipping_amount' => 3,
                    'shipping_method' => 'test shipping method',
                    'tax_amount' => 0,
                    'discount_amount' => 0,
                    'grand_total' => 0,
                    'payment' => 'test payment details',
                    'shipping_address' => 'test shipping',
                    'billing_address' => 'test billing',
                    'created_at' => $formattedDateTime,
                    'updated_at' => $formattedDateTime,
                    'customer_email' => 'customer@test.com',
                    'coupon_code' => '',
                    'status_history' => [
                        'increment_id' => 200001,
                        'created_at' => $formattedDateTime,
                        'updated_at' => $formattedDateTime,
                        'comment' => 'Test comment',
                    ],
                ],
                'expected' => [
                    'incrementId' => 1,
                    'originId' => 1000001,
                    'isVirtual' => 0,
                    'isGuest' => 1,
                    'giftMessage' => 'test gift message',
                    'remoteIp' => '127.0.0.1',
                    'storeName' => 'test store name',
                    'totalPaidAmount' => 15,
                    'totalInvoicedAmount' => 15,
                    'totalRefundedAmount' => 0,
                    'totalCanceledAmount' => 0,
                    'paymentMethod' => null,
                    'currency' => 'USD',
                    'subtotalAmount' => 24,
                    'shippingAmount' => 3,
                    'shippingMethod' => 'test shipping method',
                    'taxAmount' => 0,
                    'discountAmount' => 0,
                    'totalAmount' => 0,
                    'paymentDetails' => 'test payment details',
                    'createdAt' => $formattedDateTime,
                    'updatedAt' => $formattedDateTime,
                    'customerEmail' => 'customer@test.com',
                    'orderNotes' => [
                        [
                            'increment_id' => 200001,
                            'created_at' => $formattedDateTime,
                            'updated_at' => $formattedDateTime,
                            'comment' => 'Test comment',
                        ],
                    ],
                    'store' => [
                        'originId' => 1,
                    ],
                    'customer' => [
                        'originId' => 2,
                    ],
                    'cart' => [
                        'originId' => 1000002,
                    ],
                    'addresses' => [
                        'test shipping',
                        'test billing',
                    ],
                ],
            ],
            'test order data with multiple status_history' => [
                'data' => [
                    'increment_id' => 1,
                    'order_id' => 1000001,
                    'store_id' => 1,
                    'customer_id' => 2,
                    'is_virtual' => 0,
                    'customer_is_guest' => 1,
                    'gift_message' => 'test gift message',
                    'remote_ip' => '127.0.0.1',
                    'store_name' => 'test store name',
                    'total_paid' => 15,
                    'total_invoiced' => 15,
                    'total_refunded' => 0,
                    'total_canceled' => 0,
                    'quote_id' => 1000002,
                    'payment_method' => 'test',
                    'order_currency_code' => 'USD',
                    'subtotal' => 24,
                    'shipping_amount' => 3,
                    'shipping_method' => 'test shipping method',
                    'tax_amount' => 0,
                    'discount_amount' => 0,
                    'grand_total' => 0,
                    'payment' => 'test payment details',
                    'shipping_address' => 'test shipping',
                    'billing_address' => 'test billing',
                    'created_at' => $formattedDateTime,
                    'updated_at' => $formattedDateTime,
                    'customer_email' => 'customer@test.com',
                    'coupon_code' => '',
                    'status_history' => [
                        [
                            'increment_id' => 200001,
                            'created_at' => $formattedDateTime,
                            'updated_at' => $formattedDateTime,
                            'comment' => 'Test comment',
                        ],
                        [
                            'increment_id' => 200002,
                            'created_at' => $formattedDateTime,
                            'updated_at' => $formattedDateTime,
                            'comment' => 'Test comment 2',
                        ],

                    ],
                ],
                'expected' => [
                    'incrementId' => 1,
                    'originId' => 1000001,
                    'isVirtual' => 0,
                    'isGuest' => 1,
                    'giftMessage' => 'test gift message',
                    'remoteIp' => '127.0.0.1',
                    'storeName' => 'test store name',
                    'totalPaidAmount' => 15,
                    'totalInvoicedAmount' => 15,
                    'totalRefundedAmount' => 0,
                    'totalCanceledAmount' => 0,
                    'paymentMethod' => null,
                    'currency' => 'USD',
                    'subtotalAmount' => 24,
                    'shippingAmount' => 3,
                    'shippingMethod' => 'test shipping method',
                    'taxAmount' => 0,
                    'discountAmount' => 0,
                    'totalAmount' => 0,
                    'paymentDetails' => 'test payment details',
                    'createdAt' => $formattedDateTime,
                    'updatedAt' => $formattedDateTime,
                    'customerEmail' => 'customer@test.com',
                    'orderNotes' => [
                        [
                            'increment_id' => 200001,
                            'created_at' => $formattedDateTime,
                            'updated_at' => $formattedDateTime,
                            'comment' => 'Test comment',
                        ],
                        [
                            'increment_id' => 200002,
                            'created_at' => $formattedDateTime,
                            'updated_at' => $formattedDateTime,
                            'comment' => 'Test comment 2',
                        ],
                    ],
                    'store' => [
                        'originId' => 1,
                    ],
                    'customer' => [
                        'originId' => 2,
                    ],
                    'cart' => [
                        'originId' => 1000002,
                    ],
                    'addresses' => [
                        'test shipping',
                        'test billing',
                    ],
                ],
            ],
            'test order data without status history' => [
                'data' => [
                    'increment_id' => 1,
                    'order_id' => 1000001,
                    'store_id' => 1,
                    'customer_id' => 2,
                    'is_virtual' => 0,
                    'customer_is_guest' => 1,
                    'gift_message' => 'test gift message',
                    'remote_ip' => '127.0.0.1',
                    'store_name' => 'test store name',
                    'total_paid' => 15,
                    'total_invoiced' => 15,
                    'total_refunded' => 0,
                    'total_canceled' => 0,
                    'quote_id' => 1000002,
                    'payment_method' => 'test',
                    'order_currency_code' => 'USD',
                    'subtotal' => 24,
                    'shipping_amount' => 3,
                    'shipping_method' => 'test shipping method',
                    'tax_amount' => 0,
                    'discount_amount' => 0,
                    'grand_total' => 0,
                    'payment' => 'test payment details',
                    'shipping_address' => 'test shipping',
                    'billing_address' => 'test billing',
                    'created_at' => $formattedDateTime,
                    'updated_at' => $formattedDateTime,
                    'customer_email' => 'customer@test.com',
                    'coupon_code' => '',
                    'status_history' => [],
                ],
                'expected' => [
                    'incrementId' => 1,
                    'originId' => 1000001,
                    'isVirtual' => 0,
                    'isGuest' => 1,
                    'giftMessage' => 'test gift message',
                    'remoteIp' => '127.0.0.1',
                    'storeName' => 'test store name',
                    'totalPaidAmount' => 15,
                    'totalInvoicedAmount' => 15,
                    'totalRefundedAmount' => 0,
                    'totalCanceledAmount' => 0,
                    'paymentMethod' => null,
                    'currency' => 'USD',
                    'subtotalAmount' => 24,
                    'shippingAmount' => 3,
                    'shippingMethod' => 'test shipping method',
                    'taxAmount' => 0,
                    'discountAmount' => 0,
                    'totalAmount' => 0,
                    'paymentDetails' => 'test payment details',
                    'createdAt' => $formattedDateTime,
                    'updatedAt' => $formattedDateTime,
                    'customerEmail' => 'customer@test.com',
                    'orderNotes' => [],
                    'store' => [
                        'originId' => 1,
                    ],
                    'customer' => [
                        'originId' => 2,
                    ],
                    'cart' => [
                        'originId' => 1000002,
                    ],
                    'addresses' => [
                        'test shipping',
                        'test billing',
                    ],
                ],
            ],
            'test order data without status_history' => [
                'data' => [
                    'increment_id' => 1,
                    'order_id' => 1000001,
                    'store_id' => 1,
                    'customer_id' => 2,
                    'is_virtual' => 0,
                    'customer_is_guest' => 1,
                    'gift_message' => 'test gift message',
                    'remote_ip' => '127.0.0.1',
                    'store_name' => 'test store name',
                    'total_paid' => 15,
                    'total_invoiced' => 15,
                    'total_refunded' => 0,
                    'total_canceled' => 0,
                    'quote_id' => 1000002,
                    'payment_method' => 'test',
                    'order_currency_code' => 'USD',
                    'subtotal' => 24,
                    'shipping_amount' => 3,
                    'shipping_method' => 'test shipping method',
                    'tax_amount' => 0,
                    'discount_amount' => 0,
                    'grand_total' => 0,
                    'payment' => 'test payment details',
                    'shipping_address' => 'test shipping',
                    'billing_address' => 'test billing',
                    'created_at' => $formattedDateTime,
                    'updated_at' => $formattedDateTime,
                    'customer_email' => 'customer@test.com',
                    'coupon_code' => '',
                ],
                'expected' => [
                    'incrementId' => 1,
                    'originId' => 1000001,
                    'isVirtual' => 0,
                    'isGuest' => 1,
                    'giftMessage' => 'test gift message',
                    'remoteIp' => '127.0.0.1',
                    'storeName' => 'test store name',
                    'totalPaidAmount' => 15,
                    'totalInvoicedAmount' => 15,
                    'totalRefundedAmount' => 0,
                    'totalCanceledAmount' => 0,
                    'paymentMethod' => null,
                    'currency' => 'USD',
                    'subtotalAmount' => 24,
                    'shippingAmount' => 3,
                    'shippingMethod' => 'test shipping method',
                    'taxAmount' => 0,
                    'discountAmount' => 0,
                    'totalAmount' => 0,
                    'paymentDetails' => 'test payment details',
                    'createdAt' => $formattedDateTime,
                    'updatedAt' => $formattedDateTime,
                    'customerEmail' => 'customer@test.com',
                    'store' => [
                        'originId' => 1,
                    ],
                    'customer' => [
                        'originId' => 2,
                    ],
                    'cart' => [
                        'originId' => 1000002,
                    ],
                    'addresses' => [
                        'test shipping',
                        'test billing',
                    ],
                ],
            ],
        ];
    }

    /**
     * @return string
     */
    protected function getDateTimeNow()
    {
        if (null === $this->now) {
            $now = new \DateTime('now', new \DateTimeZone('UTC'));
            $this->now = $now->format('Y-m-d H:i:s');
        }

        return $this->now;
    }
}
