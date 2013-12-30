<?php

namespace OroCRM\Bundle\MagentoBundle\Tests\Unit\Entity;

class AddressTest extends AbstractEntityTestCase
{
    /**
     * {@inheritDoc}
     */
    public function getEntityFQCN()
    {
        return 'OroCRM\Bundle\MagentoBundle\Entity\Address';
    }

    /**
     * {@inheritDoc}
     */
    public function getSetDataProvider()
    {
        $owner      = $this->getMock('OroCRM\Bundle\MagentoBundle\Entity\Customer');
        $originId = 123;

        return [
            'owner'     => ['owner', $owner, $owner],
            'origin_id' => ['originId', $originId, $originId],
        ];
    }
}
