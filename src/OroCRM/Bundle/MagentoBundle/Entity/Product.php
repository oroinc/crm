<?php

namespace OroCRM\Bundle\MagentoBundle\Entity;

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
 *  routeName="orocrm_magento_product_index",
 *  routeView="orocrm_magento_product_view",
 *  defaultValues={
 *      "entity"={"label"="Magento Product", "plural_label"="Magento Products"},
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
     * @var float
     *
     * @ORM\Column(name="special_price", type="float", nullable=true)
     * @Oro\Versioned
     */
    protected $specialPrice;

    /**
     * @var float
     *
     * @ORM\Column(name="price", type="float", nullable=true)
     * @Oro\Versioned
     */
    protected $price;

    /**
     * @var Website
     *
     * @ORM\ManyToMany(targetEntity="OroCRM\Bundle\MagentoBundle\Entity\Website", cascade="PERSIST")
     * @ORM\JoinTable(name="orocrm_magento_product_to_website",
     *      joinColumns={@ORM\JoinColumn(name="product_id", referencedColumnName="id", onDelete="CASCADE")},
     *      inverseJoinColumns={@ORM\JoinColumn(name="website_id", referencedColumnName="id", onDelete="CASCADE")}
     * )
     */
    protected $website;

    /**
     * @param Website $website
     *
     * @return $this
     */
    public function setWebsite(Website $website)
    {
        $this->website = $website;

        return $this;
    }

    /**
     * @return Website
     */
    public function getWebsite()
    {
        return $this->website;
    }
}
