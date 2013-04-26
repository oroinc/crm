<?php

namespace Oro\Bundle\AddressBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Oro\Bundle\FlexibleEntityBundle\Entity\Mapping\AbstractEntityFlexible;
use BeSimple\SoapBundle\ServiceDefinition\Annotation as Soap;

/**
 * Address
 *
 * @ORM\MappedSuperclass
 */
class AddressBase extends AbstractEntityFlexible
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
     * @ORM\Column(name="street", type="string", length=500)
     * @Soap\ComplexType("string", nillable=true)
     */
    protected $street;

    /**
     * @var string
     *
     * @ORM\Column(name="street2", type="string", length=500, nullable=true)
     * @Soap\ComplexType("string", nillable=true)
     */
    protected $street2;

    /**
     * @var string
     *
     * @ORM\Column(name="city", type="string", length=255)
     * @Soap\ComplexType("string", nillable=true)
     */
    protected $city;

    /**
     * @var string
     *
     * @ORM\Column(name="state", type="string", length=255)
     * @ORM\ManyToOne(targetEntity="Oro\Bundle\AddressBundle\Entity\Region", cascade={"persist"})
     * @ORM\JoinColumn(name="region_id", referencedColumnName="id")
     */
    protected $state;

    /**
     * @var string
     *
     * @ORM\Column(name="postal_code", type="string", length=20)
     * @Soap\ComplexType("string", nillable=true)
     */
    protected $postalCode;

    /**
     * @var string
     *
     * @ORM\ManyToOne(targetEntity="Oro\Bundle\AddressBundle\Entity\Country", cascade={"persist"})
     * @ORM\JoinColumn(name="country_id", referencedColumnName="iso2_code")
     * @Soap\ComplexType("string", nillable=true)
     */
    protected $country;

    /**
     * Label is reserved word, so decided to use mark
     *
     * @var string
     *
     * @ORM\Column(name="mark", type="string", length=255, nullable=true)
     * @Soap\ComplexType("string", nillable=true)
     */
    protected $mark;

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
     * Set street
     *
     * @param string $street
     * @return AddressBase
     */
    public function setStreet($street)
    {
        $this->street = $street;
    
        return $this;
    }

    /**
     * Get street
     *
     * @return string 
     */
    public function getStreet()
    {
        return $this->street;
    }

    /**
     * Set street2
     *
     * @param string $street2
     * @return AddressBase
     */
    public function setStreet2($street2)
    {
        $this->street2 = $street2;
    
        return $this;
    }

    /**
     * Get street2
     *
     * @return string 
     */
    public function getStreet2()
    {
        return $this->street2;
    }

    /**
     * Set city
     *
     * @param string $city
     * @return AddressBase
     */
    public function setCity($city)
    {
        $this->city = $city;
    
        return $this;
    }

    /**
     * Get city
     *
     * @return string 
     */
    public function getCity()
    {
        return $this->city;
    }

    /**
     * Set state
     *
     * @param Region $state
     * @return AddressBase
     */
    public function setState(Region $state)
    {
        $this->state = $state;
    
        return $this;
    }

    /**
     * Get state
     *
     * @return Region
     */
    public function getState()
    {
        return $this->state;
    }

    /**
     * Set postal_code
     *
     * @param string $postalCode
     * @return AddressBase
     */
    public function setPostalCode($postalCode)
    {
        $this->postalCode = $postalCode;
    
        return $this;
    }

    /**
     * Get postal_code
     *
     * @return string 
     */
    public function getPostalCode()
    {
        return $this->postalCode;
    }

    /**
     * Set country
     *
     * @param Country $country
     * @return AddressBase
     */
    public function setCountry($country)
    {
        $this->country = $country;
    
        return $this;
    }

    /**
     * Get country
     *
     * @return Country
     */
    public function getCountry()
    {
        return $this->country;
    }

    /**
     * Set mark
     *
     * @param string $mark
     * @return AddressBase
     */
    public function setMark($mark)
    {
        $this->mark = $mark;
    
        return $this;
    }

    /**
     * Get mark
     *
     * @return string 
     */
    public function getMark()
    {
        return $this->mark;
    }

    /**
     * Get address created date/time
     *
     * @return \DateTime
     */
    public function getCreatedAt()
    {
        return $this->created;
    }

    /**
     * Get address last update date/time
     *
     * @return \DateTime
     */
    public function getUpdatedAt()
    {
        return $this->updated;
    }

    /**
     * Pre persist event listener
     *
     * @ORM\PrePersist
     */
    public function beforeSave()
    {
        $this->created = new \DateTime();
        $this->updated = new \DateTime();
    }

    /**
     * Convert address to string
     * @todo: Address format must be used here
     *
     * @return string
     */
    public function __toString()
    {
        $data = array(
            $this->getStreet(),
            $this->getStreet2(),
            ',',
            $this->getPostalCode(),
            $this->getCity(),
            ',',
            $this->getState(),
            $this->getCountry()
        );
        return implode(' ', $data);
    }
}
