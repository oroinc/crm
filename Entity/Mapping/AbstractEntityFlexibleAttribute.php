<?php
namespace Oro\Bundle\FlexibleEntityBundle\Entity\Mapping;

use Oro\Bundle\FlexibleEntityBundle\Model\AbstractFlexible;
use Oro\Bundle\FlexibleEntityBundle\Model\AbstractAttribute;
use Oro\Bundle\FlexibleEntityBundle\Model\AbstractFlexibleAttributeValue;
use Doctrine\ORM\Mapping as ORM;

/**
 * Base Doctrine ORM entity attribute (aims to add some custom properties for a attributes of a dedicated flexible)
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2012 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/MIT  MIT
 *
 */
abstract class AbstractEntityFlexibleAttribute
{
    /**
     * @var integer $id
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @var AbstractEntityAttribute
     *
     * @ORM\OneToOne(targetEntity="AbstractEntityAttribute", cascade={"persist", "merge", "remove"})
     * @ORM\JoinColumn(name="attribute_id", referencedColumnName="id")
     */
    protected $attribute;

    /**
     * Get id
     *
     * @return integer
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set id
     *
     * @param integer $id
     *
     * @return AbstractEntityFlexibleAttribute
     */
    public function setId($id)
    {
        $this->id = $id;

        return $this;
    }

    /**
     * Get attribute
     *
     * @return AbstractEntityAttribute
     */
    public function getAttribute()
    {
        return $this->attribute;
    }

    /**
     * Set attribute
     *
     * @param AbstractEntityAttribute $attribute
     *
     * @return AbstractEntityFlexibleAttribute
     */
    public function setAttribute(AbstractEntityAttribute $attribute = null)
    {
        $this->attribute = $attribute;

        return $this;
    }

}
