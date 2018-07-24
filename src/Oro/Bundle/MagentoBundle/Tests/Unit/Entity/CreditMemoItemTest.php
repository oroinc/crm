<?php

namespace Oro\Bundle\MagentoBundle\Tests\Unit\Entity;

use Oro\Bundle\MagentoBundle\Entity\CreditMemo;
use Oro\Bundle\MagentoBundle\Entity\CreditMemoItem;
use Oro\Bundle\OrganizationBundle\Entity\Organization;
use Oro\Component\Testing\Unit\EntityTestCaseTrait;

class CreditMemoItemTest extends \PHPUnit\Framework\TestCase
{
    use EntityTestCaseTrait;

    public function testAccessors()
    {
        $this->assertPropertyAccessors(new CreditMemoItem(), [
            ['id', 42],
            ['owner', new Organization()],
            ['originId', '222'],
            ['parent', new CreditMemo()],
            ['orderItemId', 12],
            ['taxAmount', 0.04],
            ['discountAmount', 0.08],
            ['rowTotal', 0.09],
            ['qty', 2],
            ['price', 0.20],
            ['additionalData', 'additional data'],
            ['description', 'description'],
            ['sku', 'AB001'],
            ['name', 'Product AB001'],
        ]);
    }
}
