<?php

namespace Oro\Bundle\SalesBundle\Entity;

interface CustomerAwareInterface
{
    /**
     * @param Customer|null $customer
     *
     * @return $this
     */
    public function setCustomerAssociation($customer = null);

    /**
     * @return Customer|null
     */
    public function getCustomerAssociation();
}
