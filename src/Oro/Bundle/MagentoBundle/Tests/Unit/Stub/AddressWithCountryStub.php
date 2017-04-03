<?php

namespace Oro\Bundle\MagentoBundle\Tests\Unit\Stub;

use Oro\Bundle\AddressBundle\Entity\Country;

class AddressWithCountryStub
{
    /**
     * @var null|Country
     */
    private $country;

    /**
     * @param Country|null $country
     */
    public function __construct(Country $country = null)
    {
        $this->country = $country;
    }

    /**
     * @return string
     */
    public function getCountryName()
    {
        return $this->country ? $this->country->getName() : '';
    }

    /**
     * @return string
     */
    public function getCountryIso3()
    {
        return $this->country ? $this->country->getIso3Code() : '';
    }

    /**
     * @return string
     */
    public function getCountryIso2()
    {
        return $this->country ? $this->country->getIso2Code() : '';
    }

    /**
     * @return null|Country
     */
    public function getCountry()
    {
        return $this->country;
    }

    /**
     * @param null|Country $country
     * @return AddressWithCountryStub
     */
    public function setCountry(Country $country)
    {
        $this->country = $country;

        return $this;
    }
}
