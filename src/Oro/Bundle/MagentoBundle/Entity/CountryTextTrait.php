<?php

namespace Oro\Bundle\MagentoBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

trait CountryTextTrait
{
    /**
     * @var string
     *
     * @ORM\Column(name="country_text", type="string", length=255, nullable=true)
     */
    protected $countryText;

    /**
     * @return string
     */
    public function getCountryText()
    {
        return $this->countryText;
    }

    /**
     * @param string $countryText
     * @return $this
     */
    public function setCountryText($countryText)
    {
        $this->countryText = $countryText;

        return $this;
    }

    /**
     * Get name of country
     *
     * @return string
     */
    public function getCountryName()
    {
        return $this->getCountry() ? parent::getCountryName() : $this->getCountryText();
    }

    /**
     * Get country ISO3 code
     *
     * @return string
     */
    public function getCountryIso3()
    {
        return $this->getCountry() ? parent::getCountryIso3() : $this->getCountryText();
    }

    /**
     * Get country ISO2 code
     *
     * @return string
     */
    public function getCountryIso2()
    {
        return $this->getCountry() ? parent::getCountryIso2() : $this->getCountryText();
    }
}
