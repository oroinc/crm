<?php

namespace OroCRM\Bundle\MagentoBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;

use Oro\Bundle\BusinessEntitiesBundle\Entity\BaseProduct;
use Oro\Bundle\DataAuditBundle\Metadata\Annotation as Oro;
use Oro\Bundle\EntityConfigBundle\Metadata\Annotation\Config;
use Oro\Bundle\IntegrationBundle\Model\IntegrationEntityTrait;

/**
 * Class Product
 *
 * @package OroCRM\Bundle\OroCRMMagentoBundle\Entity
 * @ORM\Entity
 * @ORM\Table(name="orocrm_magento_product",
 *     uniqueConstraints={@ORM\UniqueConstraint(name="unq_sku_channel_id", columns={"sku", "channel_id"})}
 * )
 * @Config(
 *  defaultValues={
 *      "security"={
 *          "type"="ACL",
 *          "group_name"=""
 *      }
 *  }
 * )
 * @Oro\Loggable
 */
class Product extends BaseProduct
{
    use IntegrationEntityTrait;

    /*
     * FIELDS are duplicated to enable dataaudit only for customer fields
     */
    /**
     * @var string
     *
     * @ORM\Column(name="name", type="string", length=255, nullable=false)
     * @Oro\Versioned
     */
    protected $name;

    /**
     * @var string
     *
     * @ORM\Column(name="sku", type="string", length=255, nullable=false)
     * @Oro\Versioned
     */
    protected $sku;

    /**
     * @var string
     *
     * @ORM\Column(name="type", type="string", length=255, nullable=false)
     * @Oro\Versioned
     */
    protected $type;

    /**
     * @var double
     *
     * @ORM\Column(name="special_price", type="money", nullable=true)
     * @Oro\Versioned
     */
    protected $specialPrice;

    /**
     * @var double
     *
     * @ORM\Column(name="price", type="money", nullable=true)
     * @Oro\Versioned
     */
    protected $price;

    /**
     * @var \DateTime $createdAt
     *
     * @ORM\Column(type="datetime", name="created_at")
     * @Oro\Versioned
     */
    protected $createdAt;

    /**
     * @var \DateTime $updatedAt
     *
     * @ORM\Column(type="datetime", name="updated_at")
     * @Oro\Versioned
     */
    protected $updatedAt;

    /**
     * @var Website[]|ArrayCollection
     *
     * @ORM\ManyToMany(targetEntity="OroCRM\Bundle\MagentoBundle\Entity\Website", cascade="PERSIST")
     * @ORM\JoinTable(name="orocrm_magento_prod_to_website",
     *      joinColumns={@ORM\JoinColumn(name="product_id", referencedColumnName="id", onDelete="CASCADE")},
     *      inverseJoinColumns={@ORM\JoinColumn(name="website_id", referencedColumnName="id", onDelete="CASCADE")}
     * )
     */
    protected $websites;

    /**
     * @var integer
     *
     * @ORM\Column(type="integer", options={"unsigned"=true}, name="origin_id")
     */
    protected $originId;

    public function __construct()
    {
        $this->websites = new ArrayCollection();
    }

    /**
     * @param float $specialPrice
     *
     * @return $this
     */
    public function setSpecialPrice($specialPrice)
    {
        $this->specialPrice = $specialPrice;

        return $this;
    }

    /**
     * @return float
     */
    public function getSpecialPrice()
    {
        return $this->specialPrice;
    }

    /**
     * @param Website $website
     *
     * @return $this
     */
    public function addWebsite(Website $website)
    {
        if (!$this->websites->contains($website)) {
            $this->websites->add($website);
        }

        return $this;
    }

    /**
     * @param Website $website
     *
     * @return $this
     */
    public function removeWebsite(Website $website)
    {
        if ($this->websites->contains($website)) {
            $this->websites->remove($website);
        }

        return $this;
    }

    /**
     * @param Website[] $websites
     *
     * @return $this
     */
    public function setWebsites(array $websites)
    {
        $this->websites = new ArrayCollection($websites);

        return $this;
    }

    /**
     * @return Website
     */
    public function getWebsites()
    {
        return $this->websites;
    }

    /**
     * @param int $originId
     *
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
