<?php

namespace Oro\Bundle\MagentoBundle\Tests\Unit\Entity;

use Oro\Bundle\AddressBundle\Entity\Country;
use Oro\Bundle\MagentoBundle\Entity\CartAddress;

class CartAddressTest extends AbstractEntityTestCase
{
    /**
     * {@inheritDoc}
     */
    public function getEntityFQCN()
    {
        return CartAddress::class;
    }

    /**
     * {@inheritDoc}
     */
    public function getSetDataProvider()
    {
        $country = new Country('US');

        return [
            'country' => ['country', $country, $country],
            'country_text' => ['countryText', 'USA', 'USA']
        ];
    }
}
