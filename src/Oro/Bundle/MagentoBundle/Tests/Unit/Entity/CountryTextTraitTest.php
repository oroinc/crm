<?php

namespace Oro\Bundle\MagentoBundle\Tests\Unit\Entity;

use Oro\Bundle\AddressBundle\Entity\Country;
use Oro\Bundle\MagentoBundle\Tests\Unit\Stub\AddressWithCountryTextStub;

class CountryTextTraitTest extends \PHPUnit\Framework\TestCase
{
    public function testCountryText()
    {
        $entity = new AddressWithCountryTextStub();
        $text = 'USA';
        $entity->setCountryText($text);
        $this->assertSame($text, $entity->getCountryText());
        $this->assertSame($text, $entity->getCountryIso2());
        $this->assertSame($text, $entity->getCountryIso3());
        $this->assertSame($text, $entity->getCountryName());
    }

    public function testCountry()
    {
        $country = new Country('US');
        $country->setIso3Code('USA');
        $country->setIso3Code('United States');

        $entity = new AddressWithCountryTextStub($country);
        $text = 'United States of America';
        $entity->setCountryText($text);
        $this->assertSame($text, $entity->getCountryText());
        $this->assertSame($country->getIso2Code(), $entity->getCountryIso2());
        $this->assertSame($country->getIso3Code(), $entity->getCountryIso3());
        $this->assertSame($country->getName(), $entity->getCountryName());
    }
}
