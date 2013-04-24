<?php

namespace Oro\Bundle\AddressBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;

/**
 * Country
 *
 * @ORM\Table("oro_dictionary_country")
 * @ORM\Entity
 */
class Country
{
    /**
     * @var string
     *
     * @ORM\Id
     * @ORM\Column(name="iso2_code", type="string", length=2)
     */
    private $iso2Code;

    /**
     * @var string
     *
     * @ORM\Column(name="iso3_code", type="string", length=3)
     */
    private $iso3Code;

    /**
     * @var string
     *
     * @ORM\Column(name="name", type="string", length=100)
     */
    private $name;

    /**
     * @var ArrayCollection
     *
     * @ORM\OneToMany(targetEntity="Region", mappedBy="country", cascade={"persist", "remove"}, orphanRemoval=true, fetch="EXTRA_LAZY")
     */
    private $regions;

    /**
     * @param null|string $name
     * @param null|string $iso2Code
     * @param null|string $iso3Code
     */
    public function __construct($name = null, $iso2Code = null, $iso3Code = null)
    {
        $this->setName($name)
             ->setIso2Code($iso2Code)
             ->setIso3Code($iso3Code);
    }

    /**
     * @param \Doctrine\Common\Collections\ArrayCollection $regions
     * @return Country
     */
    public function setRegions($regions)
    {
        $this->regions = $regions;

        return $this;
    }

    /**
     * @return \Doctrine\Common\Collections\ArrayCollection
     */
    public function getRegions()
    {
        return $this->regions;
    }

    /**
     * Set iso2_code
     *
     * @param string $iso2Code
     * @return Country
     */
    public function setIso2Code($iso2Code)
    {
        $this->iso2Code = $iso2Code;
    
        return $this;
    }

    /**
     * Get iso2_code
     *
     * @return string 
     */
    public function getIso2Code()
    {
        return $this->iso2Code;
    }

    /**
     * Set iso3_code
     *
     * @param string $iso3Code
     * @return Country
     */
    public function setIso3Code($iso3Code)
    {
        $this->iso3Code = $iso3Code;
    
        return $this;
    }

    /**
     * Get iso3_code
     *
     * @return string 
     */
    public function getIso3Code()
    {
        return $this->iso3Code;
    }

    /**
     * Set country name
     *
     * @param string $name
     * @return Country
     */
    public function setName($name)
    {
        $this->name = $name;
    
        return $this;
    }

    /**
     * Get country name
     *
     * @return string 
     */
    public function getName()
    {
        return $this->name;
    }
}
