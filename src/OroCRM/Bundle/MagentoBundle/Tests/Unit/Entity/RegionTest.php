<?php

namespace Oro\Bundle\MagentoBundle\Tests\Unit\Entity;

class RegionTest extends AbstractEntityTestCase
{
    /**
     * {@inheritDoc}
     */
    public function getEntityFQCN()
    {
        return 'Oro\Bundle\MagentoBundle\Entity\Region';
    }

    /**
     * {@inheritDoc}
     */
    public function getSetDataProvider()
    {
        $countryCode = 'US';
        $region_id = 123;
        $combinedCode = 'UA.KH';
        $name = 'Hoho';
        $regionCode = 'KH';

        return [
            'id'           => ['id',           self::TEST_ID, self::TEST_ID],
            'code'         => ['code',         $regionCode, $regionCode],
            'combinedCode' => ['combinedCode', $combinedCode, $combinedCode],
            'countryCode'  => ['countryCode',  $countryCode, $countryCode],
            'name'         => ['name',         $name, $name],
            'region_id'    => ['regionId',     $region_id, $region_id]
        ];
    }
}
