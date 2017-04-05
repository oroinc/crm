UPGRADE FROM 2.0.4 to 2.0.5
===========================

#### OroMagentoBundle

* Classes `Oro\Bundle\MagentoBundle\Form\Extension\CustomerAssociationExtension`, `Oro\Bundle\MagentoBundle\Form\Extension\OpportunityCustomerAssociationExtension`, `Oro\Bundle\MagentoBundle\EventListener\Customer\CustomerAssociationListener` were deprecated. They are no longer used.
* Added setter `setIso2CodeProvider` for `Oro\Bundle\MagentoBundle\ImportExport\Converter\AbstractAddressDataConverter`

#### OroCaseBundle
* Search index fields `description`, `resolution` and `message` for `CaseEntity` now contain no more than 255 characters
  each. Please, run reindexation for this entity using command
  `php app/console oro:search:reindex OroCaseBundle:CaseEntity --env=prod`
