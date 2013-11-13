<?php

namespace OroCRM\Bundle\MagentoBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

use Oro\Bundle\EntityConfigBundle\Metadata\Annotation\Config;

use Oro\Bundle\LocaleBundle\Model\FullNameInterface;
use Oro\Bundle\BusinessEntitiesBundle\Entity\BaseCustomerEntity;

/**
 * Class MagentoCustomer
 *
 * @package OroCRM\Bundle\OroCRMMagentoBundle\Entity
 * @ORM\Entity
 * @ORM\Table(name="orocrm_magento_customer")
 * @Config(
 *  routeName="orocrm_magento_customer_index",
 *  routeView="orocrm_magento_customer_view",
 *  defaultValues={
 *      "entity"={"label"="Customer", "plural_label"="Customers"},
 *      "ownership"={
 *          "owner_type"="USER",
 *          "owner_field_name"="owner",
 *          "owner_column_name"="user_owner_id"
 *      },
 *      "security"={
 *          "type"="ACL",
 *          "group_name"=""
 *      }
 *  }
 * )
 */
class MagentoCustomer extends BaseCustomerEntity implements FullNameInterface
{
    /**
     * @var \DateTime $createdAt
     *
     * @ORM\Column(type="datetime")
     */
    protected $createdAt;

    /**
     * @var \DateTime $updatedAt
     *
     * @ORM\Column(type="datetime")
     */
    protected $updatedAt;

    /**
     * @var MagentoWebsite
     *
     * @ORM\ManyToOne(targetEntity="OroCRM\Bundle\MagentoBundle\Entity\MagentoWebsite")
     * @ORM\JoinColumn(name="website_id", referencedColumnName="id", onDelete="SET NULL")
     */
    protected $website;

    /**
     * @var MagentoStore
     *
     * @ORM\ManyToOne(targetEntity="OroCRM\Bundle\MagentoBundle\Entity\MagentoStore")
     * @ORM\JoinColumn(name="store_id", referencedColumnName="id", onDelete="SET NULL")
     */
    protected $store;

    /**
     * @var MagentoCustomerGroup
     *
     * @ORM\ManyToOne(targetEntity="OroCRM\Bundle\MagentoBundle\Entity\MagentoCustomerGroup")
     * @ORM\JoinColumn(name="customer_group_id", referencedColumnName="id", onDelete="SET NULL")
     */
    protected $group;

    /**
     * @param \DateTime $createdAt
     *
     * @return $this
     */
    public function setCreatedAt(\DateTime $createdAt)
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    /**
     * @return \DateTime
     */
    public function getCreatedAt()
    {
        return $this->createdAt;
    }

    /**
     * @param \DateTime $updatedAt
     *
     * @return $this
     */
    public function setUpdatedAt(\DateTime $updatedAt)
    {
        $this->updatedAt = $updatedAt;

        return $this;
    }

    /**
     * @return \DateTime
     */
    public function getUpdatedAt()
    {
        return $this->updatedAt;
    }

    /**
     * @param MagentoWebsite $website
     *
     * @return $this
     */
    public function setWebsite($website)
    {
        $this->website = $website;

        return $this;
    }

    /**
     * @return MagentoWebsite
     */
    public function getWebsite()
    {
        return $this->website;
    }

    /**
     * @param MagentoStore $store
     *
     * @return $this
     */
    public function setStore(MagentoStore $store)
    {
        $this->store = $store;

        return $this;
    }

    /**
     * @return MagentoStore
     */
    public function getStore()
    {
        return $this->store;
    }

    /**
     * @param MagentoCustomerGroup $group
     *
     * @return $this
     */
    public function setGroup(MagentoCustomerGroup $group)
    {
        $this->group = $group;

        return $this;
    }

    /**
     * @return MagentoCustomerGroup
     */
    public function getGroup()
    {
        return $this->group;
    }
}
