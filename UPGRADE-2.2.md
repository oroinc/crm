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
- Implementation of REST API for customer association was changed.
    - removed the following classes:
        - `Oro\Bundle\SalesBundle\Api\Processor\CustomerAssociationCustomizeLoadedData`
        - `Oro\Bundle\SalesBundle\Api\Processor\CustomerAssociationFinalize`
        - `Oro\Bundle\SalesBundle\Api\Processor\CustomerAssociationGetConfig`
        - `Oro\Bundle\SalesBundle\Api\Processor\CustomerAssociationGetMetadata`
        - `Oro\Bundle\SalesBundle\Api\Processor\GetConfig`
        - `Oro\Bundle\SalesBundle\Api\Processor\InitializeCustomerAccountTypeGuesser`
        - `Oro\Bundle\SalesBundle\Api\Processor\InitializeCustomerTypeGuesser`
        - `Oro\Bundle\SalesBundle\Form\Guesser\CustomerAccountApiTypeGuesser`
        - `Oro\Bundle\SalesBundle\Form\Guesser\CustomerApiTypeGuesser`
        - `Oro\Bundle\SalesBundle\Form\Type\CustomerAccountApiType`
        - `Oro\Bundle\SalesBundle\Form\Type\CustomerApiType`
    - removed the following services:
        - `oro_sales.api.get_config.customer_association`
        - `oro_sales.api.customize_loaded_data.customer_association`
        - `oro_sales.api.get_metadata.customer_association`
        - `oro_sales.api.get_metadata.get_config`
        - `oro_sales.api.load_data.customer_association`
        - `oro_sales.api.opportunity.initialize_customer_type_guesser.customer_association`
        - `oro_sales.api.opportunity.initialize_customer_account_type_guesser`
        - `oro_sales.form.guesser.customer_guesser`
        - `oro_sales.form.guesser.customer_account_guesser`
        - `oro_sales.form.type.customer_api` (API form type alias `oro_sales_customer_api`)
        - `oro_sales.form.type.customer_account_api` (API form type alias `oro_sales_customer_account_api`)
    - the logic related to the customer association is implemented in the following API processors:
        - `Oro\Bundle\SalesBundle\Api\Processor\AddCustomerAssociationFormListener`
        - `Oro\Bundle\SalesBundle\Api\Processor\AddCustomerAssociationAccountFormListener`
        - `Oro\Bundle\SalesBundle\Api\Processor\AddCustomerAssociationCustomerFormListener`

MagentoBundle
-----------
- Classes `Oro\Bundle\MagentoBundle\Form\Extension\CustomerAssociationExtension`, `Oro\Bundle\MagentoBundle\Form\Extension\OpportunityCustomerAssociationExtension`, `Oro\Bundle\MagentoBundle\EventListener\Customer\CustomerAssociationListener` were removed. They are no longer used.
- Added setter `setIso2CodeProvider` for `Oro\Bundle\MagentoBundle\ImportExport\Converter\AbstractAddressDataConverter`

CaseBundle
------------
- Search index fields `description`, `resolution` and `message` for `CaseEntity` now contain no more than 255 characters
  each. Please, run reindexation for this entity using command
  `php app/console oro:search:reindex OroCaseBundle:CaseEntity --env=prod`
