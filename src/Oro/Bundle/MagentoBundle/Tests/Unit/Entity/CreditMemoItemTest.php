<?php

namespace Oro\Bundle\MagentoBundle\Tests\Unit\Entity;

use Symfony\Component\PropertyAccess\PropertyAccess;

use Oro\Bundle\CalendarBundle\Tests\Unit\ReflectionUtil;
use Oro\Bundle\MagentoBundle\Entity\CreditMemo;
use Oro\Bundle\MagentoBundle\Entity\CreditMemoItem;
use Oro\Bundle\MagentoBundle\Entity\Product;
use Oro\Bundle\MagentoBundle\Entity\OrderItem;
use Oro\Bundle\OrganizationBundle\Entity\Organization;

class CreditMemoItemTest extends \PHPUnit_Framework_TestCase
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
        $obj = new CreditMemoItem();

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
            ['itemId', '111'],
            ['parent', new CreditMemo()],
            ['product', new Product()],
            ['orderItem', new OrderItem()],
            ['weeeTaxAppliedRowAmount', 0.01],
            ['basePrice', 0.02],
            ['baseWeeeTaxRowDisposition', 0.03],
            ['taxAmount', 0.04],
            ['baseWeeeTaxAppliedAmount', 0.05],
            ['weeeTaxRowDisposition', 0.06],
            ['baseRowTotal', 0.07],
            ['discountAmount', 0.08],
            ['rowTotal', 0.09],
            ['weeeRaxAppliedAmount', 0.10],
            ['baseDiscountAmount', 0.11],
            ['baseWeeeTaxDisposition', 0.12],
            ['priceInclTax', 0.13],
            ['baseTaxAmount', 0.14],
            ['weeeTaxDisposition', 0.15],
            ['basePriceInclTax', 0.16],
            ['qty', 0.17],
            ['baseCost', 0.18],
            ['baseWeeeTaxAppliedRowAmount', 0.19],
            ['price', 0.20],
            ['baseRowTotalInclTax', 0.21],
            ['rowTotalInclTax', 0.22],
            ['additionalData', 'additional data'],
            ['description', 'description'],
            ['weeeTaxApplied', 0.23],
            ['sku', 'AB001'],
            ['name', 'Product AB001'],
            ['hiddenTaxAmount', 0.24],
            ['baseHiddenTaxAmount', 0.25],
            ['owner', new Organization()],
        ];
    }
}
