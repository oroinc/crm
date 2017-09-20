UPGRADE FROM 2.4 to 2.5
========================

Table of Contents
-----------------

- [MagentoBundle](#magentobundle)

MagentoBundle
-------------
- class `Oro\Bundle\MagentoBundle\ImportExport\Strategy` 
    - property `addressRegions` was removed to `Oro\Bundle\MagentoBundle\ImportExport\Strategy\StrategyHelper\AddressImportHelper` and renamed to `mageRegionsIds`
- class `Oro\Bundle\MagentoBundle\ImportExport\Strategy\StrategyHelper\AddressImportHelper` 
    - changed `updateRegionByMagentoRegionId` signature: parameter `countryCode` was removed. parameter `$mageRegionId` was replaced by `$originId`
    - changed `getAddressCountryByCode` signature: parameter `countryCode` was removed. 
    - changed `updateAddressCountryRegion` signature: parameter `$mageRegionId` was replaced by `$originId`
    - new public method added: `addMageRegionId`
