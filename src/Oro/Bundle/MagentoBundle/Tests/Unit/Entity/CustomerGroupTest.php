<?php

namespace Oro\Bundle\MagentoBundle\Tests\Unit\Entity;

class CustomerGroupTest extends AbstractEntityTestCase
{
    const TEST_NAME = 'groupName';

    /**
     * {@inheritDoc}
     */
    public function getEntityFQCN()
    {
        return 'Oro\Bundle\MagentoBundle\Entity\CustomerGroup';
    }

    /**
     * {@inheritDoc}
     */
    public function getSetDataProvider()
    {
        return [
            'id'   => ['id'],
            'name' => ['name', self::TEST_NAME, self::TEST_NAME],
        ];
    }
}
