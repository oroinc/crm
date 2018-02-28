<?php

namespace Oro\Bundle\MagentoBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Oro\Bundle\EntityConfigBundle\Metadata\Annotation\Config;
use Oro\Bundle\EntityConfigBundle\Metadata\Annotation\ConfigField;
use Oro\Bundle\MagentoBundle\Model\ExtendProduct;

/**
 * Class Product
 *
 * @package Oro\Bundle\OroMagentoBundle\Entity
 * @ORM\Entity
 * @ORM\Table(name="orocrm_magento_product")
 * @Config(
 *      defaultValues={
 *          "security"={
 *              "type"="ACL",
 *              "group_name"="",
 *              "category"="sales_data"
 *          },
 *          "note"={
 *              "immutable"=true
 *          },
 *          "activity"={
 *              "immutable"=true
 *          },
 *          "attachment"={
 *              "immutable"=true
 *          }
 *      }
 * )
 */
class Product extends ExtendProduct implements IntegrationAwareInterface
{
    use IntegrationEntityTrait;

    /*
     * FIELDS are duplicated to enable dataaudit only for customer fields
     */
    /**
     * @var string
     *
     * @ORM\Column(name="name", type="string", length=255, nullable=false)
     */
    protected $name;

    /**
     * @var string
     *
     * @ORM\Column(name="sku", type="string", length=255, nullable=true)
     */
    protected $sku;

    /**
     * @var string
     *
     * @ORM\Column(name="type", type="string", length=255, nullable=false)
     */
    protected $type;

    /**
     * @var double
     *
     * @ORM\Column(name="special_price", type="money", nullable=true)
     */
    protected $specialPrice;

    /**
     * @var double
     *
     * @ORM\Column(name="price", type="money", nullable=true)
     */
    protected $price;

    /**
     * @var \DateTime $createdAt
     *
     * @ORM\Column(type="datetime", name="created_at")
     * @ConfigField(
     *      defaultValues={
     *          "entity"={
     *              "label"="oro.ui.created_at"
     *          }
     *      }
     * )
     */
    protected $createdAt;

    /**
     * @var \DateTime $updatedAt
     *
     * @ORM\Column(type="datetime", name="updated_at")
     * @ConfigField(
     *      defaultValues={
     *          "entity"={
     *              "label"="oro.ui.updated_at"
     *          }
     *      }
     * )
     */
    protected $updatedAt;

    /**
     * @var Website[]|ArrayCollection
     *
     * @ORM\ManyToMany(targetEntity="Oro\Bundle\MagentoBundle\Entity\Website")
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
        parent::__construct();

        $this->websites = new ArrayCollection();
    }

    /**
     * @param float $specialPrice
     *
     * @return Product
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
     * @return Product
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
     * @return Product
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
     * @return Product
     */
    public function setWebsites(array $websites)
    {
        $this->websites = new ArrayCollection($websites);

        return $this;
    }

    /**
     * @return Website[]
     */
    public function getWebsites()
    {
        return $this->websites;
    }

    /**
     * @param int $originId
     *
     * @return Product
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

    /**
     * @return string
     */
    public function __toString()
    {
        return (string)$this->getName();
    }
}
