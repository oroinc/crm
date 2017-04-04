UPGRADE FROM 2.1 to 2.1.1
========================

#### OroMagentoBundle

* Added setter `setIso2CodeProvider` for `Oro\Bundle\MagentoBundle\ImportExport\Converter\AbstractAddressDataConverter`

### OroCaseBundle
* Search index fields `description`, `resolution` and `message` for `CaseEntity` now contain no more than 255 characters
  each. Please, run reindexation for this entity using command
  `php app/console oro:search:reindex OroCaseBundle:CaseEntity --env=prod`
