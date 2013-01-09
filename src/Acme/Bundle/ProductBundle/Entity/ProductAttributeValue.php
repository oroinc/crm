<?php
namespace Acme\Bundle\ProductBundle\Entity;

use Oro\Bundle\FlexibleEntityBundle\Entity\Mapping\AbstractEntityFlexibleAttributeValue;
use Doctrine\ORM\Mapping as ORM;

/**
 * Value for a product attribute
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2012 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/MIT MIT
 *
 * @ORM\Table(name="acmeproduct_product_attribute_value")
 * @ORM\Entity
 */
class ProductAttributeValue extends AbstractEntityFlexibleAttributeValue
{
    /**
     * @var Oro\Bundle\FlexibleEntityBundle\Model\Entity\AbstractAttribute $attribute
     *
     * @ORM\ManyToOne(targetEntity="Oro\Bundle\FlexibleEntityBundle\Entity\OrmAttribute")
     */
    protected $attribute;

    /**
     * @var Oro\Bundle\FlexibleEntityBundle\Model\Entity\AbstractFlexible $entity
     *
     * @ORM\ManyToOne(targetEntity="Product", inversedBy="values")
     */
    protected $entity;

    /**
     * Store option value, if backend is an option
     *
     * @var Oro\Bundle\FlexibleEntityBundle\Model\Entity\AbstractAttributeOption $optionvalue
     *
     * @ORM\ManyToOne(targetEntity="Oro\Bundle\FlexibleEntityBundle\Entity\OrmAttributeOption")
     */
    protected $option;
}
