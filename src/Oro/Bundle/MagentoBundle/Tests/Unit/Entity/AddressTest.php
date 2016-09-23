<?php

namespace Oro\Bundle\MagentoBundle\Tests\Unit\Entity;

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
        $owner      = $this->getMock('Oro\Bundle\MagentoBundle\Entity\Customer');
        $originId = 123;

        return [
            'owner'     => ['owner', $owner, $owner],
            'origin_id' => ['originId', $originId, $originId],
        ];
    }
}
