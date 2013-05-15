<?php

namespace Oro\Bundle\FlexibleEntityBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

use Oro\Bundle\FlexibleEntityBundle\Entity\Mapping\AbstractEntityFlexibleValue;

/**
 * Email
 *
 * @ORM\Table(name="oro_flexibleentity_email")
 * @ORM\Entity
 */
class Email
{
    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @var string
     *
     * @ORM\Column(name="data", type="string", length=255)
     */
    private $data;

    /**
     * @var integer
     *
     * @ORM\Column(name="type", type="smallint")
     */
    private $type;

    /**
     * @ORM\ManyToOne(targetEntity="Oro\Bundle\AddressBundle\Entity\Value\AddressValue", inversedBy="emails")
     * @ORM\JoinColumn(name="value_id", referencedColumnName="id")
     * @var AbstractEntityFlexibleValue
     */
    private $value;

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
     * @param int $id
     * @return $this
     */
    public function setId($id)
    {
        $this->id = $id;

        return $this;
    }

    /**
     * Set data
     *
     * @param string $data
     * @return Email
     */
    public function setData($data)
    {
        $this->data = $data;
    
        return $this;
    }

    /**
     * Get data
     *
     * @return string 
     */
    public function getData()
    {
        return $this->data;
    }

    /**
     * Set type
     *
     * @param integer $type
     * @return Email
     */
    public function setType($type)
    {
        $this->type = $type;
    
        return $this;
    }

    /**
     * Get type
     *
     * @return integer 
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * Set value
     *
     * @param AbstractEntityFlexibleValue $value
     * @return $this
     */
    public function setValue(AbstractEntityFlexibleValue $value)
    {
        $this->value = $value;

        return $this;
    }

    /**
     * Get value
     *
     * @return AbstractEntityFlexibleValue
     */
    public function getValue()
    {
        return $this->value;
    }

    /**
     * To string
     *
     * @return string
     */
    public function __toString()
    {
        return $this->getData();
    }
}
