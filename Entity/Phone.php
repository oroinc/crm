<?php

namespace Oro\Bundle\FlexibleEntityBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Phone
 *
 * @ORM\Table("oro_flexibleentity_phone")
 * @ORM\Entity
 */
class Phone
{
    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     * @Soap\ComplexType("int", nillable=true)
     */
    protected $id;

    /**
     * @var string
     *
     * @ORM\Column(name="phone", type="string", length=50)
     * @Soap\ComplexType("string", nillable=true)
     */
    private $phone;

    /**
     * @var string
     *
     * @ORM\Column(name="area_code", type="string", length=10)
     * @Soap\ComplexType("string", nillable=true)
     */
    private $area_code;

    /**
     * @var string
     *
     * @ORM\Column(name="type", type="string", length=50)
     * @Soap\ComplexType("string", nillable=true)
     */
    private $type;

    /**
     * @param null|string $phone
     * @param null $areaCode
     * @param null|string $type
     */
    public function __construct($phone = null, $areaCode = null, $type = null)
    {
        $this->setPhone($phone);
        $this->setAreaCode($areaCode);
        $this->setType($type);
    }

    /**
     * Set type
     *
     * @param string type
     * @return Phone
     */
    public function setType($type)
    {
        $this->type = $type;

        return $this;
    }

    /**
     * Get type
     *
     * @return string
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * Set phone number
     *
     * @param string $phone
     * @return Phone
     */
    public function setPhone($phone)
    {
        $this->phone = $phone;

        return $this;
    }

    /**
     * Get phone number
     *
     * @return string
     */
    public function getPhone()
    {
        return $this->phone;
    }

    /**
     * @param string $area_code
     * @return Phone
     */
    public function setAreaCode($area_code)
    {
        $this->area_code = $area_code;

        return $this;
    }

    /**
     * @return string
     */
    public function getAreaCode()
    {
        return $this->area_code;
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return (is_null($this->getAreaCode()) ? '' : $this->getAreaCode() . ' ') . $this->getPhone();
    }
}
