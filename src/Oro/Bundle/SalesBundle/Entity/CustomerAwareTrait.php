<?php

namespace Oro\Bundle\SalesBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

trait CustomerAwareTrait
{
    /**
     * @var Customer|null
     *
     * @ORM\ManyToOne(targetEntity="Customer", cascade={"persist"})
     * @ORM\JoinColumn(name="customer_association_id", referencedColumnName="id", onDelete="CASCADE")
     */
    protected $customerAssociation;

    /**
     * @param Customer|null $customer
     *
     * @return $this
     */
    public function setCustomerAssociation($customer = null)
    {
        $this->customerAssociation = $customer;

        return $this;
    }

    /**
     * @return Customer|null
     */
    public function getCustomerAssociation()
    {
        return $this->customerAssociation;
    }
}
