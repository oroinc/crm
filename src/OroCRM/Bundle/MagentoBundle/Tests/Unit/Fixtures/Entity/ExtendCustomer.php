<?php

namespace OroCRM\Bundle\MagentoBundle\Tests\Unit\Fixtures\Entity;

use OroCRM\Bundle\MagentoBundle\Entity\Customer;

class ExtendCustomer extends Customer
{
    /**
     * @param int $id
     */
    public function setId($id)
    {
        $this->id = $id;
    }
}
