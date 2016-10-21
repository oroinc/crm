<?php

namespace Oro\Bundle\MagentoBundle\Tests\Unit\Fixtures\Entity;

use Oro\Bundle\MagentoBundle\Entity\Customer;

class ExtendCustomer extends Customer
{
    /**
     * @param int $id
     *
     * @return void
     */
    public function setId($id)
    {
        $this->id = $id;
    }
}
