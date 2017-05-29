UPGRADE FROM 2.1 to 2.2
========================

Table of Contents
-----------------

- [CaseBundle](#casebundle)
- [ContactBundle](#contactbundle)
- [MagentoBundle](#magentobundle)
- [SalesBundle](#salesbundle)


CaseBundle
------------
- Search index fields `description`, `resolution` and `message` for `CaseEntity` now contain no more than **255** characters each. 
  
    Please, run re-indexation for this entity using command:
  
    ```bash
      php app/console oro:search:reindex OroCaseBundle:CaseEntity --env=prod
    ```

ContactBundle
-------------
* The `HasContactInformation::$message`<sup>[[?]](https://github.com/orocrm/crm/tree/2.1.0/src/Oro/Bundle/ContactBundle/Validator/Constraints/HasContactInformation.php#L10 "Oro\Bundle\ContactBundle\Validator\Constraints\HasContactInformation::$message")</sup> property was removed.

MagentoBundle
-------------
* The following classes were removed:
   - `CustomerAssociationExtension`<sup>[[?]](https://github.com/orocrm/crm/tree/2.1.0/src/Oro/Bundle/MagentoBundle/Form/Extension/CustomerAssociationExtension.php#L18 "Oro\Bundle\MagentoBundle\Form\Extension\CustomerAssociationExtension")</sup>
   - `OpportunityCustomerAssociationExtension`<sup>[[?]](https://github.com/orocrm/crm/tree/2.1.0/src/Oro/Bundle/MagentoBundle/Form/Extension/OpportunityCustomerAssociationExtension.php#L18 "Oro\Bundle\MagentoBundle\Form\Extension\OpportunityCustomerAssociationExtension")</sup>
   - `CustomerAssociationListener`<sup>[[?]](https://github.com/orocrm/crm/tree/2.1.0/src/Oro/Bundle/MagentoBundle/EventListener/Customer/CustomerAssociationListener.php#L12 "Oro\Bundle\MagentoBundle\EventListener\Customer\CustomerAssociationListener")</sup>
* The `SyncCartExpirationIntegrationProcessor::__construct(RegistryInterface $doctrine, CartExpirationProcessor $cartExpirationProcessor, JobRunner $jobRunner, LoggerInterface $logger)`<sup>[[?]](https://github.com/orocrm/crm/tree/2.1.0/src/Oro/Bundle/MagentoBundle/Async/SyncCartExpirationIntegrationProcessor.php#L46 "Oro\Bundle\MagentoBundle\Async\SyncCartExpirationIntegrationProcessor")</sup> method was changed to `SyncCartExpirationIntegrationProcessor::__construct(RegistryInterface $doctrine, CartExpirationProcessor $cartExpirationProcessor, JobRunner $jobRunner, TokenStorageInterface $tokenStorage, LoggerInterface $logger)`<sup>[[?]](https://github.com/orocrm/crm/tree/2.2.0/src/Oro/Bundle/MagentoBundle/Async/SyncCartExpirationIntegrationProcessor.php#L51 "Oro\Bundle\MagentoBundle\Async\SyncCartExpirationIntegrationProcessor")</sup>
* The `SyncInitialIntegrationProcessor::__construct(DoctrineHelper $doctrineHelper, InitialSyncProcessor $initialSyncProcessor, OptionalListenerManager $optionalListenerManager, CalculateAnalyticsScheduler $calculateAnalyticsScheduler, JobRunner $jobRunner, IndexerInterface $indexer, LoggerInterface $logger)`<sup>[[?]](https://github.com/orocrm/crm/tree/2.1.0/src/Oro/Bundle/MagentoBundle/Async/SyncInitialIntegrationProcessor.php#L72 "Oro\Bundle\MagentoBundle\Async\SyncInitialIntegrationProcessor")</sup> method was changed to `SyncInitialIntegrationProcessor::__construct(DoctrineHelper $doctrineHelper, InitialSyncProcessor $initialSyncProcessor, OptionalListenerManager $optionalListenerManager, CalculateAnalyticsScheduler $calculateAnalyticsScheduler, JobRunner $jobRunner, IndexerInterface $indexer, TokenStorageInterface $tokenStorage, LoggerInterface $logger)`<sup>[[?]](https://github.com/orocrm/crm/tree/2.2.0/src/Oro/Bundle/MagentoBundle/Async/SyncInitialIntegrationProcessor.php#L76 "Oro\Bundle\MagentoBundle\Async\SyncInitialIntegrationProcessor")</sup>

SalesBundle
-----------
* Implementation of REST API for customer association was changed.
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
    - the logic related to the customer association is implemented in `CustomerAssociationListener`<sup>[[?]](https://github.com/laboro/dev/blob/maintenance/2.2/package/crm/src/Oro/Bundle/SalesBundle/Api/Form/EventListener/CustomerAssociationListener.php "Oro\Bundle\SalesBundle\Api\Form\EventListener\CustomerAssociationListener")</sup>
* The following classes were removed:
   - `CustomerAccountApiType`<sup>[[?]](https://github.com/orocrm/crm/tree/2.1.0/src/Oro/Bundle/SalesBundle/Form/Type/CustomerAccountApiType.php#L13 "Oro\Bundle\SalesBundle\Form\Type\CustomerAccountApiType")</sup>
   - `CustomerApiType`<sup>[[?]](https://github.com/orocrm/crm/tree/2.1.0/src/Oro/Bundle/SalesBundle/Form/Type/CustomerApiType.php#L12 "Oro\Bundle\SalesBundle\Form\Type\CustomerApiType")</sup>
   - `CustomerAccountApiTypeGuesser`<sup>[[?]](https://github.com/orocrm/crm/tree/2.1.0/src/Oro/Bundle/SalesBundle/Form/Guesser/CustomerAccountApiTypeGuesser.php#L10 "Oro\Bundle\SalesBundle\Form\Guesser\CustomerAccountApiTypeGuesser")</sup>
   - `CustomerApiTypeGuesser`<sup>[[?]](https://github.com/orocrm/crm/tree/2.1.0/src/Oro/Bundle/SalesBundle/Form/Guesser/CustomerApiTypeGuesser.php#L12 "Oro\Bundle\SalesBundle\Form\Guesser\CustomerApiTypeGuesser")</sup>
   - `LeadStatus`<sup>[[?]](https://github.com/orocrm/crm/tree/2.1.0/src/Oro/Bundle/SalesBundle/Entity/LeadStatus.php#L22 "Oro\Bundle\SalesBundle\Entity\LeadStatus")</sup>
   - `OpportunityStatus`<sup>[[?]](https://github.com/orocrm/crm/tree/2.1.0/src/Oro/Bundle/SalesBundle/Entity/OpportunityStatus.php#L22 "Oro\Bundle\SalesBundle\Entity\OpportunityStatus")</sup>
   - `WidgetLeadStatusSelectConverter`<sup>[[?]](https://github.com/orocrm/crm/tree/2.1.0/src/Oro/Bundle/SalesBundle/Dashboard/Converters/WidgetLeadStatusSelectConverter.php#L8 "Oro\Bundle\SalesBundle\Dashboard\Converters\WidgetLeadStatusSelectConverter")</sup>
   - `CustomerAssociationCustomizeLoadedData`<sup>[[?]](https://github.com/orocrm/crm/tree/2.1.0/src/Oro/Bundle/SalesBundle/Api/Processor/CustomerAssociationCustomizeLoadedData.php#L13 "Oro\Bundle\SalesBundle\Api\Processor\CustomerAssociationCustomizeLoadedData")</sup>
   - `CustomerAssociationFinalize`<sup>[[?]](https://github.com/orocrm/crm/tree/2.1.0/src/Oro/Bundle/SalesBundle/Api/Processor/CustomerAssociationFinalize.php#L10 "Oro\Bundle\SalesBundle\Api\Processor\CustomerAssociationFinalize")</sup>
   - `CustomerAssociationGetConfig`<sup>[[?]](https://github.com/orocrm/crm/tree/2.1.0/src/Oro/Bundle/SalesBundle/Api/Processor/CustomerAssociationGetConfig.php#L11 "Oro\Bundle\SalesBundle\Api\Processor\CustomerAssociationGetConfig")</sup>
   - `CustomerAssociationGetMetadata`<sup>[[?]](https://github.com/orocrm/crm/tree/2.1.0/src/Oro/Bundle/SalesBundle/Api/Processor/CustomerAssociationGetMetadata.php#L11 "Oro\Bundle\SalesBundle\Api\Processor\CustomerAssociationGetMetadata")</sup>
   - `GetConfig`<sup>[[?]](https://github.com/orocrm/crm/tree/2.1.0/src/Oro/Bundle/SalesBundle/Api/Processor/GetConfig.php#L8 "Oro\Bundle\SalesBundle\Api\Processor\GetConfig")</sup>
   - `InitializeCustomerAccountTypeGuesser`<sup>[[?]](https://github.com/orocrm/crm/tree/2.1.0/src/Oro/Bundle/SalesBundle/Api/Processor/InitializeCustomerAccountTypeGuesser.php#L12 "Oro\Bundle\SalesBundle\Api\Processor\InitializeCustomerAccountTypeGuesser")</sup>
   - `InitializeCustomerTypeGuesser`<sup>[[?]](https://github.com/orocrm/crm/tree/2.1.0/src/Oro/Bundle/SalesBundle/Api/Processor/InitializeCustomerTypeGuesser.php#L14 "Oro\Bundle\SalesBundle\Api\Processor\InitializeCustomerTypeGuesser")</sup>
* The `ConfigProvider::getCustomersData`<sup>[[?]](https://github.com/orocrm/crm/tree/2.1.0/src/Oro/Bundle/SalesBundle/Provider/Customer/ConfigProvider.php#L56 "Oro\Bundle\SalesBundle\Provider\Customer\ConfigProvider::getCustomersData")</sup> method was removed.
* The `CustomerController::customerGridAction`<sup>[[?]](https://github.com/orocrm/crm/tree/2.1.0/src/Oro/Bundle/SalesBundle/Controller/CustomerController.php#L55 "Oro\Bundle\SalesBundle\Controller\CustomerController::customerGridAction")</sup> method was removed.
* The `CustomerController::getCustomersData`<sup>[[?]](https://github.com/orocrm/crm/tree/2.1.0/src/Oro/Bundle/SalesBundle/Controller/CustomerController.php#L88 "Oro\Bundle\SalesBundle\Controller\CustomerController::getCustomersData")</sup> method was removed.
* The `CustomerType::__construct(DataTransformerInterface $transformer, ConfigProvider $customerConfigProvider, EntityAliasResolver $entityAliasResolver, CustomerIconProviderInterface $customerIconProvider, TranslatorInterface $translator, SecurityFacade $securityFacade, ManagerInterface $gridManager, EntityNameResolver $entityNameResolver)`<sup>[[?]](https://github.com/orocrm/crm/tree/2.1.0/src/Oro/Bundle/SalesBundle/Form/Type/CustomerType.php#L64 "Oro\Bundle\SalesBundle\Form\Type\CustomerType")</sup> method was changed to `CustomerType::__construct(DataTransformerInterface $transformer, ConfigProvider $customerConfigProvider, EntityAliasResolver $entityAliasResolver, CustomerIconProviderInterface $customerIconProvider, TranslatorInterface $translator, SecurityFacade $securityFacade, ManagerInterface $gridManager, EntityNameResolver $entityNameResolver, MultiGridProvider $multiGridProvider)`<sup>[[?]](https://github.com/orocrm/crm/tree/2.2.0/src/Oro/Bundle/SalesBundle/Form/Type/CustomerType.php#L66 "Oro\Bundle\SalesBundle\Form\Type\CustomerType")</sup>
* The `B2bCustomerType::setDefaultOptions`<sup>[[?]](https://github.com/orocrm/crm/tree/2.1.0/src/Oro/Bundle/SalesBundle/Form/Type/B2bCustomerType.php#L101 "Oro\Bundle\SalesBundle\Form\Type\B2bCustomerType::setDefaultOptions")</sup> method was removed.
* The `Lead::$address`<sup>[[?]](https://github.com/orocrm/crm/tree/2.1.0/src/Oro/Bundle/SalesBundle/Entity/Lead.php#L334 "Oro\Bundle\SalesBundle\Entity\Lead::$address")</sup> property was removed.
* The following methods in class `Lead`<sup>[[?]](https://github.com/orocrm/crm/tree/2.2.0/src/Oro/Bundle/SalesBundle/Entity/Lead.php "Oro\Bundle\SalesBundle\Entity\Lead")</sup> were removed:
   - `hasAddress`<sup>[[?]](https://github.com/orocrm/crm/tree/2.1.0/src/Oro/Bundle/SalesBundle/Entity/Lead.php#L860 "Oro\Bundle\SalesBundle\Entity\Lead::hasAddress")</sup>
   - `setAddress`<sup>[[?]](https://github.com/orocrm/crm/tree/2.1.0/src/Oro/Bundle/SalesBundle/Entity/Lead.php#L871 "Oro\Bundle\SalesBundle\Entity\Lead::setAddress")</sup>
