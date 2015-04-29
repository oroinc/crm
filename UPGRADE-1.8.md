UPGRADE FROM 1.7 to 1.8
=======================

####OroCRMMagentoBundle:
- Class `OroCRM\Bundle\MagentoBundle\ImportExport\Strategy\AbstractImportStrategy` is not abstract anymore as it
don't requires any method implementation
- Class `OroCRM\Bundle\MagentoBundle\ImportExport\Strategy\CustomerAddressStrategy` removed in favor of reused
`OroCRM\Bundle\MagentoBundle\ImportExport\Strategy\AbstractImportStrategy`
