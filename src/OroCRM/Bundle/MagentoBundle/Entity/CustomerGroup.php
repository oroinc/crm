<?php

namespace OroCRM\Bundle\MagentoBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

use Oro\Bundle\DataAuditBundle\Metadata\Annotation as Oro;
use Oro\Bundle\BusinessEntitiesBundle\Entity\BasePersonGroup;
use Oro\Bundle\IntegrationBundle\Model\IntegrationEntityTrait;

/**
 * Class CustomerGroup
 *
 * @package OroCRM\Bundle\OroCRMMagentoBundle\Entity
 * @ORM\Entity
 * @Oro\Loggable
 * @ORM\Table(name="orocrm_magento_customer_group")
 */
class CustomerGroup extends BasePersonGroup
{
    use IntegrationEntityTrait, OriginTrait;

    /**
     * @var string
     *
     * @ORM\Column(name="name", type="string", length=255, nullable=false)
     * @Oro\Versioned
     */
    protected $name;
}
