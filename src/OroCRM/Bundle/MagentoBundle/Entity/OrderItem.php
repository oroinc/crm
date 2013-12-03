<?php

namespace OroCRM\Bundle\MagentoBundle\Entity;

/**
 * Class OrderItem
 *
 * @package OroCRM\Bundle\OroCRMMagentoBundle\Entity
 * @ORM\Entity
 * @ORM\Table(name="orocrm_magento_order_item")
 * @Config(
 *  routeName="orocrm_magento_orderitem_index",
 *  routeView="orocrm_magento_orderitem_view",
 *  defaultValues={
 *      "entity"={"label"="Magento Order Item", "plural_label"="Magento Order Items"},
 *      "security"={
 *          "type"="ACL",
 *          "group_name"=""
 *      }
 *  }
 * )
 * @Oro\Loggable
 */
class OrderItem
{
    /**
     * @var int
     *
     * @ORM\Id
     * @ORM\Column(type="integer", name="id")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;
}
