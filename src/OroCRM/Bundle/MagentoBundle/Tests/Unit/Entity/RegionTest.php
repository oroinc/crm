<?php

namespace OroCRM\Bundle\MagentoBundle\Tests\Unit\Entity;

class RegionTest extends AbstractEntityTestCase
{
    /**
     * {@inheritDoc}
     */
    public function getEntityFQCN()
    {
        return 'OroCRM\Bundle\MagentoBundle\Entity\Region';
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
