<?php
namespace Oro\Bundle\FlexibleEntityBundle\Tests\Entity\Demo;

use Oro\Bundle\FlexibleEntityBundle\Entity\Mapping\AbstractEntityFlexibleValue;
use Doctrine\ORM\Mapping as ORM;

/**
 * A concret flexible attribue value class
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2012 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/MIT MIT
 *
 * @ORM\Entity()
 */
class FlexibleValue extends AbstractEntityFlexibleValue
{

    /**
     * @var Oro\Bundle\FlexibleEntityBundle\Entity\Attribute $attribute
     *
     * @ORM\ManyToOne(targetEntity="Oro\Bundle\FlexibleEntityBundle\Entity\Attribute")
     */
    protected $attribute;

    /**
     * @var Product $entity
     *
     * @ORM\ManyToOne(targetEntity="Flexible", inversedBy="values")
     */
    protected $entity;

    /**
     * Store options values
     *
     * @var options ArrayCollection
     *
     * @ORM\ManyToMany(targetEntity="Oro\Bundle\FlexibleEntityBundle\Entity\AttributeOption")
     */
    protected $options;
}
