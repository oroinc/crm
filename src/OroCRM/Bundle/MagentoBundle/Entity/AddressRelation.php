<?php

namespace OroCRM\Bundle\MagentoBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

use OroCRM\Bundle\ContactBundle\Entity\ContactAddress;

/**
 * Class Region
 *
 * @package OroCRM\Bundle\OroCRMMagentoBundle\Entity
 * @ORM\Entity
 * @ORM\Table(
 *  name="orocrm_magento_address_rel",
 *  indexes={
 *      @ORM\Index(name="idx_rel", columns={"origin_id"})
 *  },
 *  uniqueConstraints={@ORM\UniqueConstraint(name="unq_origin", columns={"origin_id"})}
 * )
 */
class AddressRelation
{
    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @var ContactAddress
     *
     * @ORM\OneToOne(targetEntity="OroCRM\Bundle\ContactBundle\Entity\ContactAddress", cascade={"persist"})
     * @ORM\JoinColumn(name="address_id", referencedColumnName="id", nullable=false)
     */
    protected $address;

    /**
     * @var integer
     *
     * @ORM\Column(name="origin_id", type="integer")
     */
    protected $originId;

    /**
     * @param ContactAddress $address
     * @return $this
     */
    public function setAddress($address)
    {
        $this->address = $address;

        return $this;
    }

    /**
     * @return ContactAddress
     */
    public function getAddress()
    {
        return $this->address;
    }

    /**
     * @param int $id
     * @return $this
     */
    public function setId($id)
    {
        $this->id = $id;

        return $this;
    }

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param int $originId
     * @return $this
     */
    public function setOriginId($originId)
    {
        $this->originId = $originId;

        return $this;
    }

    /**
     * @return int
     */
    public function getOriginId()
    {
        return $this->originId;
    }
}
