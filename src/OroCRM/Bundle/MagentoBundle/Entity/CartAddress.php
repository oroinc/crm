<?php

namespace OroCRM\Bundle\MagentoBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

use Oro\Bundle\DataAuditBundle\Metadata\Annotation as Oro;
use Oro\Bundle\EntityConfigBundle\Metadata\Annotation\Config;
use OroCRM\Bundle\MagentoBundle\Model\ExtendCartAddress;

/**
 * @ORM\Table("orocrm_magento_cart_address")
 * @ORM\HasLifecycleCallbacks()
 * @Config(
 *       defaultValues={
 *          "entity"={"icon"="icon-map-marker"},
 *      }
 * )
 * @ORM\Entity
 * @Oro\Loggable
 */
class CartAddress extends ExtendCartAddress
{
    use OriginTrait;
}
