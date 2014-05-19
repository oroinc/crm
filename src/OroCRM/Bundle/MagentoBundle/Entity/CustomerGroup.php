<?php

namespace OroCRM\Bundle\MagentoBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

use Oro\Bundle\DataAuditBundle\Metadata\Annotation as Oro;
use Oro\Bundle\BusinessEntitiesBundle\Entity\BasePersonGroup;
use Oro\Bundle\IntegrationBundle\Model\IntegrationEntityTrait;

use Oro\Bundle\EntityConfigBundle\Metadata\Annotation\Config;

/**
 * Class CustomerGroup
 *
 * @package OroCRM\Bundle\OroCRMMagentoBundle\Entity
 * @ORM\Entity
 * @ORM\Table(name="orocrm_magento_customer_group")
 * @Config(
 *   defaultValues={
 *      "entity"={"icon"="icon-group"}
 *  }
 * )
 * @Oro\Loggable
 */
class CustomerGroup extends BasePersonGroup
{
    use IntegrationEntityTrait;
    use OriginTrait;

    /**
     * @var string
     *
     * @ORM\Column(name="name", type="string", length=255, nullable=false)
     * @Oro\Versioned
     */
    protected $name;
}
