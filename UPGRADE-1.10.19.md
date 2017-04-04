UPGRADE FROM 1.10.18 to 1.10.19
===============================

#### OroCRMCaseBundle

* Search index fields `description`, `resolution` and `message` for `CaseEntity` now contain no more than 255 characters
  each. Please, run reindexation for this entity using command
  `php app/console oro:search:reindex OroCRMCaseBundle:CaseEntity --env=prod`

#### OroCRMMagentoBundle

* Added setter `setIso2CodeProvider` for `OroCRM\Bundle\MagentoBundle\ImportExport\Converter\AbstractAddressDataConverter`