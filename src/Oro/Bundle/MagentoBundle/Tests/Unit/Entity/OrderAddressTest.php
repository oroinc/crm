<?php

namespace Oro\Bundle\MagentoBundle\Tests\Unit\Entity;

use Oro\Bundle\AddressBundle\Entity\Country;
use Oro\Bundle\MagentoBundle\Entity\OrderAddress;

class OrderAddressTest extends AbstractEntityTestCase
{
    /**
     * {@inheritDoc}
     */
    public function getEntityFQCN()
    {
        return OrderAddress::class;
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
