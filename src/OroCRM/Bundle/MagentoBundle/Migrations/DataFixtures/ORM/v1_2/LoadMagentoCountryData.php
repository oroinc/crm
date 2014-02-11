<?php

namespace OroCRM\Bundle\MagentoBundle\Migrations\DataFixtures\ORM\v1_2;

use Oro\Bundle\AddressBundle\Migrations\DataFixtures\ORM\v1_0\LoadCountryData;

class LoadMagentoCountryData extends LoadCountryData
{
    /**
     * @return string
     */
    protected function getFileName()
    {
        $fileName = __DIR__ . $this->structureFileName;
        $fileName = str_replace('/', DIRECTORY_SEPARATOR, $fileName);

        return $fileName;
    }
}
