<?php

namespace Oro\Bundle\MagentoBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Oro\Bundle\EntityConfigBundle\Metadata\Annotation\Config;
use Oro\Bundle\MagentoBundle\Model\ExtendCustomerGroup;

/**
 * Class CustomerGroup
 *
 * @package Oro\Bundle\OroMagentoBundle\Entity
 * @ORM\Entity
 * @ORM\Table(name="orocrm_magento_customer_group")
 * @Config(
 *      defaultValues={
 *          "entity"={
 *              "icon"="fa-users"
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
class CustomerGroup extends ExtendCustomerGroup implements OriginAwareInterface, IntegrationAwareInterface
{
    use IntegrationEntityTrait, OriginTrait;

    /**
     * @var string
     *
     * @ORM\Column(name="name", type="string", length=255, nullable=false)
     */
    protected $name;

    /**
     * @return string
     */
    public function __toString()
    {
        return (string) $this->name;
    }
}
