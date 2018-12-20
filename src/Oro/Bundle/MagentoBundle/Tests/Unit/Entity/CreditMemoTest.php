<?php

namespace Oro\Bundle\MagentoBundle\Tests\Unit\Entity;

use Oro\Bundle\MagentoBundle\Entity\CreditMemo;
use Oro\Bundle\MagentoBundle\Entity\CreditMemoItem;
use Oro\Bundle\MagentoBundle\Entity\Order;
use Oro\Bundle\MagentoBundle\Entity\Store;
use Oro\Bundle\OrganizationBundle\Entity\Organization;
use Oro\Bundle\UserBundle\Entity\User;
use Oro\Component\Testing\Unit\EntityTestCaseTrait;

class CreditMemoTest extends \PHPUnit\Framework\TestCase
{
    use EntityTestCaseTrait;

    public function testAccessors()
    {
        $this->assertPropertyAccessors(new CreditMemo(), [
            ['id', 42],
            ['owner', new User()],
            ['incrementId', '111'],
            ['originId', '222'],
            ['invoiceId', 333],
            ['transactionId', '444'],
            ['order', new Order()],
            ['store', new Store()],
            ['owner', new User()],
            ['organization', new Organization()],
            ['emailSent', true],
            ['adjustment', 0.06],
            ['subtotal', 0.07],
            ['adjustmentNegative', 0.14],
            ['shippingAmount', 0.16],
            ['grandTotal', 0.19],
            ['adjustmentPositive', 0.24],
            ['customerBalTotalRefunded', 0.34],
            ['rewardPointsBalanceRefund', 0.52],
            ['updatedAt', new \DateTime()],
            ['createdAt', new \DateTime()],
            ['syncedAt', new \DateTime()],
            ['importedAt', new \DateTime()],
        ]);
    }

    public function testCollections()
    {
        $item = new CreditMemoItem();

        $this->assertPropertyCollection(new CreditMemo(), 'items', $item);
    }
}
