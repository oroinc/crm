<?php

namespace OroCRM\Bundle\B2CMockBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

use OroCRM\Bundle\MagentoBundle\Entity\Customer;

/**
 * @ORM\Table("orocrm_b2c_sale_order")
 * @ORM\Entity
 */
class SaleOrder
{
    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @var Customer
     *
     * @ORM\ManyToOne(targetEntity="OroCRM\Bundle\MagentoBundle\Entity\Customer")
     * @ORM\JoinColumn(name="customer_id", referencedColumnName="id", onDelete="SET NULL", nullable=true)
     */
    protected $customer;

    /**
     * Get id
     *
     * @return integer 
     */
    public function getId()
    {
        return $this->id;
    }
}
