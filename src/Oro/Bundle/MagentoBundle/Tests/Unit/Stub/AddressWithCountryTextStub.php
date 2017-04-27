<?php

namespace Oro\Bundle\MagentoBundle\Tests\Unit\Stub;

use Oro\Bundle\MagentoBundle\Entity\CountryTextTrait;

class AddressWithCountryTextStub extends AddressWithCountryStub
{
    use CountryTextTrait;
}
