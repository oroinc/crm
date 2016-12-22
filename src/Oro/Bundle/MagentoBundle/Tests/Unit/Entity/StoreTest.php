<?php

namespace Oro\Bundle\MagentoBundle\Tests\Unit\Entity;

class StoreTest extends AbstractEntityTestCase
{
    const TEST_STORE_CODE = 'scode';
    const TEST_STORE_NAME = 'sname';

    /**
     * {@inheritDoc}
     */
    public function getEntityFQCN()
    {
        return 'Oro\Bundle\MagentoBundle\Entity\Store';
    }

    /**
     * {@inheritDoc}
     */
    public function getSetDataProvider()
    {
        $website = $this->createMock('Oro\Bundle\MagentoBundle\Entity\Website');

        return [
            'id'      => ['id', self::TEST_ID, self::TEST_ID],
            'code'    => ['code', self::TEST_STORE_CODE, self::TEST_STORE_CODE],
            'name'    => ['name', self::TEST_STORE_NAME, self::TEST_STORE_NAME],
            'website' => ['website', $website, $website]
        ];
    }

    public function getGetWebsiteName()
    {
        $this->assertNull($this->entity->getWebsiteName());

        $expectedValue = 'test';
        $website = $this->createMock('Oro\Bundle\MagentoBundle\Entity\Website');
        $website->expects($this->once())
            ->method('getName')
            ->will($this->returnValue($expectedValue));

        $this->assertEquals($expectedValue, $website->getWebsiteName());
    }
}
