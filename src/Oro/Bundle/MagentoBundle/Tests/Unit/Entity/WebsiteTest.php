<?php

namespace Oro\Bundle\MagentoBundle\Tests\Unit\Entity;

class WebsiteTest extends AbstractEntityTestCase
{
    const TEST_WEBSITE_CODE = 'wcode';
    const TEST_WEBSITE_NAME = 'wname';

    /**
     * {@inheritDoc}
     */
    public function getEntityFQCN()
    {
        return 'Oro\Bundle\MagentoBundle\Entity\Website';
    }

    /**
     * {@inheritDoc}
     */
    public function getSetDataProvider()
    {
        return [
            'id'   => ['id', self::TEST_ID, self::TEST_ID],
            'code' => ['code', self::TEST_WEBSITE_CODE, self::TEST_WEBSITE_CODE],
            'name' => ['name', self::TEST_WEBSITE_NAME, self::TEST_WEBSITE_NAME]
        ];
    }
}
