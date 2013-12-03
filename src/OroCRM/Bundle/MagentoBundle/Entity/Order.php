<?php

namespace OroCRM\Bundle\MagentoBundle\Entity;

/**
 * Class Order
 *
 * @package OroCRM\Bundle\OroCRMMagentoBundle\Entity
 * @ORM\Entity
 * @ORM\Table(name="orocrm_magento_order")
 * @Config(
 *  routeName="orocrm_magento_order_index",
 *  routeView="orocrm_magento_order_view",
 *  defaultValues={
 *      "entity"={"label"="Magento Order", "plural_label"="Magento Orders"},
 *      "security"={
 *          "type"="ACL",
 *          "group_name"=""
 *      }
 *  }
 * )
 * @Oro\Loggable
 */
class Order
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
