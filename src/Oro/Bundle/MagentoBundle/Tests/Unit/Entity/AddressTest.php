<?php

namespace Oro\Bundle\MagentoBundle\Tests\Unit\Entity;

use Oro\Bundle\AddressBundle\Entity\Country;

class AddressTest extends AbstractEntityTestCase
{
    /**
     * {@inheritDoc}
     */
    public function getEntityFQCN()
    {
        return 'Oro\Bundle\MagentoBundle\Entity\Address';
    }

    /**
     * {@inheritDoc}
     */
    public function getSetDataProvider()
    {
        $owner      = $this->createMock('Oro\Bundle\MagentoBundle\Entity\Customer');
        $originId = 123;
        $country = new Country('US');

        return [
            'owner'     => ['owner', $owner, $owner],
            'origin_id' => ['originId', $originId, $originId],
            'country' => ['country', $country, $country],
            'country_text' => ['countryText', 'USA', 'USA']
        ];
    }
}
