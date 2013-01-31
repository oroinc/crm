<?php
namespace Oro\Bundle\FlexibleEntityBundle\Tests\Entity\Demo;

use Oro\Bundle\FlexibleEntityBundle\Entity\Mapping\AbstractEntityAttributeExtended;
use Oro\Bundle\FlexibleEntityBundle\Entity\Attribute;
use Doctrine\ORM\Mapping as ORM;

/**
 * A concret flexible attribute class
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2012 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/MIT MIT
 *
 * @ORM\Entity()
 */
class FlexibleAttribute extends AbstractEntityAttributeExtended
{

    /**
     * @var Oro\Bundle\FlexibleEntityBundle\Entity\Attribute $attribute
     *
     * @ORM\OneToOne(targetEntity="Oro\Bundle\FlexibleEntityBundle\Entity\Attribute", cascade={"persist", "merge", "remove"})
     * @ORM\JoinColumn(name="attribute_id", referencedColumnName="id")
     */
    protected $attribute;

    /**
     * @var string $name
     */
    protected $name;

    /**
     * @var string $description
     */
    protected $description;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->description = '';
        $this->smart       = false;
    }

    /**
     * Get name
     * @return string $name
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Set name
     * @param string $name
     */
    public function setName($name)
    {
        $this->name = $name;
    }

    /**
     * Get description
     * @return string $description
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * Set description
     * @param string $description
     */
    public function setDescription($description)
    {
        $this->description = $description;
    }

    /**
     * Get searchable
     *
     * @return boolean $smart
     */
    public function getSmart()
    {
        return $this->smart;
    }

    /**
     * Set smart
     *
     * @param boolean $smart
     *
     * @return ProductAttribute
     */
    public function setSmart($smart)
    {
        $this->smart = $smart;

        return $this;
    }

}
