<?php

namespace Oro\Bundle\MagentoBundle\Tests\Unit\Entity;

use Doctrine\Common\Collections\ArrayCollection;

use Symfony\Component\PropertyAccess\PropertyAccess;

use Oro\Bundle\MagentoBundle\Entity\CreditMemo;
use Oro\Bundle\MagentoBundle\Entity\Order;
use Oro\Bundle\MagentoBundle\Entity\OrderAddress;
use Oro\Bundle\MagentoBundle\Entity\Store;
use Oro\Bundle\UserBundle\Entity\User;
use Oro\Bundle\CalendarBundle\Tests\Unit\ReflectionUtil;
use Oro\Bundle\OrganizationBundle\Entity\Organization;

class CreditMemoTest extends \PHPUnit_Framework_TestCase
{
    public function testIdGetter()
    {
        $obj = new CreditMemo();
        ReflectionUtil::setId($obj, 1);
        $this->assertEquals(1, $obj->getId());
    }

    /**
     * @dataProvider propertiesDataProvider
     *
     * @param string $property
     * @param mixed  $value
     */
    public function testSettersAndGetters($property, $value)
    {
        $obj = new CreditMemo();

        $accessor = PropertyAccess::createPropertyAccessor();
        $accessor->setValue($obj, $property, $value);
        $this->assertSame($value, $accessor->getValue($obj, $property));
    }

    /**
     * @return array
     */
    public function propertiesDataProvider()
    {
        return [
            ['incrementId', '111'],
            ['creditMemoId', '222'],
            ['invoiceId', '333'],
            ['transactionId', '444'],
            ['order', new Order()],
            ['store', new Store()],
            ['owner', new User()],
            ['organization', new Organization()],
            ['status', 'status_123'],
            ['emailSent', true],
            ['globalCurrencyCode', 'USD'],
            ['baseCurrencyCode', 'USD'],
            ['orderCurrencyCode', 'USD'],
            ['storeCurrencyCode', 'USD'],
            ['cybersourceToken', 'this-is-not-token'],
            ['addresses', new ArrayCollection([new OrderAddress()])],
            ['state', 'state'],
            ['taxAmount', 0.01],
            ['shippingTaxAmount', 0.02],
            ['baseTaxAmount', 0.03],
            ['baseAdjustmentPositive', 0.04],
            ['baseGrandTotal', 0.05],
            ['adjustment', 0.06],
            ['subtotal', 0.07],
            ['discountAmount', 0.08],
            ['baseSubtotal', 0.09],
            ['baseAdjustment', 0.10],
            ['baseToGlobalRate', 0.11],
            ['storeToBaseRate', 0.12],
            ['baseShippingAmount', 0.13],
            ['adjustmentNegative', 0.14],
            ['subtotalInclTax', 0.15],
            ['shippingAmount', 0.16],
            ['baseSubtotalInclTax', 0.17],
            ['baseAdjustmentNegative', 0.18],
            ['grandTotal', 0.19],
            ['baseDiscountAmount', 0.20],
            ['baseToOrderRate', 0.21],
            ['storeToOrderRate', 0.22],
            ['baseShippingTaxAmount', 0.23],
            ['adjustmentPositive', 0.24],
            ['hiddenTaxAmount', 0.25],
            ['baseHiddenTaxAmount', 0.26],
            ['shippingHiddenTaxAmount', 0.27],
            ['baseShippingHiddenTaxAmnt', 0.28],
            ['shippingInclTax', 0.29],
            ['baseShippingInclTax', 0.30],
            ['baseCustomerBalanceAmount', 0.31],
            ['customerBalanceAmount', 0.32],
            ['bsCustomerBalTotalRefunded', 0.33],
            ['customerBalTotalRefunded', 0.34],
            ['baseGiftCardsAmount', 0.35],
            ['giftCardsAmount', 0.36],
            ['gwBasePrice', 0.37],
            ['gwPrice', 0.38],
            ['gwItemsBasePrice', 0.39],
            ['gwItemsPrice', 0.40],
            ['gwCardBasePrice', 0.41],
            ['gwCardPrice', 0.42],
            ['gwBaseTaxAmount', 0.43],
            ['gwTaxAmount', 0.44],
            ['gwItemsBaseTaxAmount', 0.45],
            ['gwItemsTaxAmount', 0.46],
            ['gwCardBaseTaxAmount', 0.47],
            ['gwCardTaxAmount', 0.48],
            ['baseRewardCurrencyAmount', 0.49],
            ['rewardCurrencyAmount', 0.50],
            ['rewardPointsBalance', 0.51],
            ['rewardPointsBalanceRefund', 0.52],
            ['updatedAt', new \DateTime()],
            ['createdAt', new \DateTime()],
        ];
    }
}
