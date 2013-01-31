<?php

namespace Oro\Bundle\UserBundle\Entity;

use Oro\Bundle\FlexibleEntityBundle\Entity\Mapping\AbstractEntityFlexibleValue;
use Oro\Bundle\FlexibleEntityBundle\Entity\Attribute;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Table(name="user_value")
 * @ORM\Entity
 */
class UserValue extends AbstractEntityFlexibleValue
{
    /**
     * @var Oro\Bundle\FlexibleEntityBundle\Entity\Attribute $attribute
     *
     * @ORM\ManyToOne(targetEntity="Oro\Bundle\FlexibleEntityBundle\Entity\Attribute")
     * @ORM\JoinColumn(name="attribute_id", referencedColumnName="id", onDelete="CASCADE")
     */
    protected $attribute;

    /**
     * @var User $entity
     *
     * @ORM\ManyToOne(targetEntity="User", inversedBy="values")
     */
    protected $entity;

    /**
     * Store values data when backend is option (deal to select, multi-select)
     *
     * @var options ArrayCollection
     *
     * @ORM\ManyToMany(targetEntity="Oro\Bundle\FlexibleEntityBundle\Entity\AttributeOption")
     * @ORM\JoinTable(
     *     name="user_value_option",
     *     joinColumns={@ORM\JoinColumn(name="value_id", referencedColumnName="id", onDelete="CASCADE")},
     *     inverseJoinColumns={@ORM\JoinColumn(name="option_id", referencedColumnName="id", onDelete="CASCADE")}
     * )
     */
    protected $options;
}