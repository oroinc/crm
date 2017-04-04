UPGRADE FROM 2.1 to 2.2
========================

MagentoBundle
-------------
- Class `Oro\Bundle\MagentoBundle\Async\SyncInitialIntegrationProcessor`
    - construction signature was changed now it takes next arguments:
        - `DoctrineHelper` $doctrineHelper,
        - `InitialSyncProcessor` $initialSyncProcessor,
        - `OptionalListenerManager` $optionalListenerManager,
        - `CalculateAnalyticsScheduler` $calculateAnalyticsScheduler,
        - `JobRunner` $jobRunner,
        - `IndexerInterface` $indexer,
        - `TokenStorageInterface` $tokenStorage,
        - `LoggerInterface` $logger
- Class `Oro\Bundle\MagentoBundle\Async\SyncCartExpirationIntegrationProcessor`
    - construction signature was changed now it takes next arguments:
        `RegistryInterface` $doctrine,
        `CartExpirationProcessor` $cartExpirationProcessor,
        `JobRunner` $jobRunner,
        `TokenStorageInterface` $tokenStorage,
        `LoggerInterface` $logger

SalesBundle
-----------
- Class `Oro\Bundle\SalesBundle\Provider\Customer\ConfigProvider`
    - method `getCustomersData` was removed. Use `getGridCustomersData` instead.
- Class `Oro\Bundle\SalesBundle\Controller\CustomerController`
    - action `gridDialogAction` is rendered in `OroDataGridBundle:Grid/dialog:multi.html.twig`
    - action `customerGridAction` was removed
    - method `getCustomersData` was removed
- Class `Oro\Bundle\SalesBundle\Form\Type\CustomerType`
    - construction signature was changed, now it takes the next arguments:
        - `DataTransformerInterface` $transformer
        - `ConfigProvider` $customerConfigProvider
        - `EntityAliasResolver` $entityAliasResolver
        - `CustomerIconProviderInterface` $customerIconProvider
        - `TranslatorInterface` $translator
        - `SecurityFacade` $securityFacade
        - `ManagerInterface` $gridManager
        - `EntityNameResolver` $entityNameResolver
        - `MultiGridProvider` $multiGridProvider
- Class `Oro\Bundle\SalesBundle\Entity\LeadStatus` removed
- Class `Oro\Bundle\SalesBundle\Entity\OpportunityStatus` removed
- Class `Oro\Bundle\SalesBundle\Entity\Lead`
    - field `address` removed
    - methods `hasAddress`, `setAddress` removed

MagentoBundle
-----------
- Classes `Oro\Bundle\MagentoBundle\Form\Extension\CustomerAssociationExtension`, `Oro\Bundle\MagentoBundle\Form\Extension\OpportunityCustomerAssociationExtension`, `Oro\Bundle\MagentoBundle\EventListener\Customer\CustomerAssociationListener` were removed. They are no longer used.

CaseBundle
------------
- Search index fields `description`, `resolution` and `message` for `CaseEntity` now contain no more than 255 characters
  each. Please, run reindexation for this entity using command
  `php app/console oro:search:reindex OroCaseBundle:CaseEntity --env=prod`
