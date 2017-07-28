UPGRADE FROM 2.2 to 2.3
========================

Table of Contents
-----------------

- [General](#general)
- [AccountBundle](#accountbundle)
- [CalendarCRM](#calendarcrm)
- [CaseBundle](#casebundle)
- [ChannelBundle](#channelbundle)
- [ContactBundle](#contactbundle)
- [MagentoBundle](#magentobundle)
- [MarketingCRM](#marketingcrm)
- [SalesBundle](#salesbundle)

General
-------

### Important

The class `Oro\Bundle\SecurityBundle\SecurityFacade`, services `oro_security.security_facade` and `oro_security.security_facade.link`, and TWIG function `resource_granted` were marked as deprecated.
Use services `security.authorization_checker`, `security.token_storage`, `oro_security.token_accessor`, `oro_security.class_authorization_checker`, `oro_security.request_authorization_checker` and TWIG function `is_granted` instead.
In controllers use `isGranted` method from `Symfony\Bundle\FrameworkBundle\Controller\Controller`.
The usage of deprecated service `security.context` (interface `Symfony\Component\Security\Core\SecurityContextInterface`) was removed as well.
All existing classes were updated to use new services instead of the `SecurityFacade` and `SecurityContext`:

- service `security.authorization_checker`
    - implements `Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface`
    - the property name in classes that use this service is `authorizationChecker`
- service `security.token_storage`
    - implements `Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface`
    - the property name in classes that use this service is `tokenStorage`
- service `oro_security.token_accessor`
    - implements `Oro\Bundle\SecurityBundle\Authentication\TokenAccessorInterface`
    - the property name in classes that use this service is `tokenAccessor`
- service `oro_security.class_authorization_checker`
    - implements `Oro\Bundle\SecurityBundle\Authorization\ClassAuthorizationChecker`
    - the property name in classes that use this service is `classAuthorizationChecker`
- service `oro_security.request_authorization_checker`
    - implements `Oro\Bundle\SecurityBundle\Authorization\RequestAuthorizationChecker`
    - the property name in classes that use this service is `requestAuthorizationChecker`

AccountBundle
-------------
* The `AccountType::__construct(Router $router, EntityNameResolver $entityNameResolver, SecurityFacade $securityFacade)`<sup>[[?]](https://github.com/oroinc/crm/tree/2.2.0/src/Oro/Bundle/AccountBundle/Form/Type/AccountType.php#L47 "Oro\Bundle\AccountBundle\Form\Type\AccountType")</sup> method was changed to `AccountType::__construct(RouterInterface $router, EntityNameResolver $entityNameResolver, AuthorizationCheckerInterface $authorizationChecker)`<sup>[[?]](https://github.com/oroinc/crm/tree/2.3.0/src/Oro/Bundle/AccountBundle/Form/Type/AccountType.php#L35 "Oro\Bundle\AccountBundle\Form\Type\AccountType")</sup>
* The `AccountType::$securityFacade`<sup>[[?]](https://github.com/oroinc/crm/tree/2.2.0/src/Oro/Bundle/AccountBundle/Form/Type/AccountType.php#L35 "Oro\Bundle\AccountBundle\Form\Type\AccountType::$securityFacade")</sup> property was removed.

CalendarCRM
-----------
* The `LoadUsersCalendarData::$securityContext`<sup>[[?]](https://github.com/oroinc/crm/tree/2.2.0/src/Oro/Bridge/CalendarCRM/Migrations/Data/Demo/ORM/LoadUsersCalendarData.php#L47 "Oro\Bridge\CalendarCRM\Migrations\Data\Demo\ORM\LoadUsersCalendarData::$securityContext")</sup> property was removed.

CaseBundle
----------
* The `ViewFactory::__construct(SecurityFacade $securityFacade, RouterInterface $router, EntityNameResolver $entityNameResolver, DateTimeFormatter $dateTimeFormatter, AttachmentManager $attachmentManager)`<sup>[[?]](https://github.com/oroinc/crm/tree/2.2.0/src/Oro/Bundle/CaseBundle/Model/ViewFactory.php#L56 "Oro\Bundle\CaseBundle\Model\ViewFactory")</sup> method was changed to `ViewFactory::__construct(AuthorizationCheckerInterface $authorizationChecker, RouterInterface $router, EntityNameResolver $entityNameResolver, DateTimeFormatter $dateTimeFormatter, AttachmentManager $attachmentManager)`<sup>[[?]](https://github.com/oroinc/crm/tree/2.3.0/src/Oro/Bundle/CaseBundle/Model/ViewFactory.php#L44 "Oro\Bundle\CaseBundle\Model\ViewFactory")</sup>
* The `ViewFactory::$securityFacade`<sup>[[?]](https://github.com/oroinc/crm/tree/2.2.0/src/Oro/Bundle/CaseBundle/Model/ViewFactory.php#L22 "Oro\Bundle\CaseBundle\Model\ViewFactory::$securityFacade")</sup> property was removed.

ChannelBundle
-------------
* The `StateProvider::__construct(SettingsProvider $settingsProvider, Cache $cache, RegistryInterface $registry, ServiceLink $securityFacadeLink, AclHelper $aclHelper)`<sup>[[?]](https://github.com/oroinc/crm/tree/2.2.0/src/Oro/Bundle/ChannelBundle/Provider/StateProvider.php#L43 "Oro\Bundle\ChannelBundle\Provider\StateProvider")</sup> method was changed to `StateProvider::__construct(SettingsProvider $settingsProvider, Cache $cache, RegistryInterface $registry, TokenAccessorInterface $tokenAccessor, AclHelper $aclHelper)`<sup>[[?]](https://github.com/oroinc/crm/tree/2.3.0/src/Oro/Bundle/ChannelBundle/Provider/StateProvider.php#L42 "Oro\Bundle\ChannelBundle\Provider\StateProvider")</sup>
* The `StateProvider::$securityFacadeLink`<sup>[[?]](https://github.com/oroinc/crm/tree/2.2.0/src/Oro/Bundle/ChannelBundle/Provider/StateProvider.php#L34 "Oro\Bundle\ChannelBundle\Provider\StateProvider::$securityFacadeLink")</sup> property was removed.

ContactBundle
-------------
* The `ContactAddStrategy::setSecurityContext`<sup>[[?]](https://github.com/oroinc/crm/tree/2.2.0/src/Oro/Bundle/ContactBundle/ImportExport/Strategy/ContactAddStrategy.php#L35 "Oro\Bundle\ContactBundle\ImportExport\Strategy\ContactAddStrategy::setSecurityContext")</sup> method was removed.
* The `ContactAddStrategy::$securityContext`<sup>[[?]](https://github.com/oroinc/crm/tree/2.2.0/src/Oro/Bundle/ContactBundle/ImportExport/Strategy/ContactAddStrategy.php#L22 "Oro\Bundle\ContactBundle\ImportExport\Strategy\ContactAddStrategy::$securityContext")</sup> property was removed.
* The `ContactEmailApiHandler::$securityFacade`<sup>[[?]](https://github.com/oroinc/crm/tree/2.2.0/src/Oro/Bundle/ContactBundle/Handler/ContactEmailApiHandler.php#L28 "Oro\Bundle\ContactBundle\Handler\ContactEmailApiHandler::$securityFacade")</sup> property was removed.
* The `ContactPhoneApiHandler::$securityFacade`<sup>[[?]](https://github.com/oroinc/crm/tree/2.2.0/src/Oro/Bundle/ContactBundle/Handler/ContactPhoneApiHandler.php#L28 "Oro\Bundle\ContactBundle\Handler\ContactPhoneApiHandler::$securityFacade")</sup> property was removed.
* The `ContactEmailHandler::$securityFacade`<sup>[[?]](https://github.com/oroinc/crm/tree/2.2.0/src/Oro/Bundle/ContactBundle/Form/Handler/ContactEmailHandler.php#L32 "Oro\Bundle\ContactBundle\Form\Handler\ContactEmailHandler::$securityFacade")</sup> property was removed.
* The `ContactPhoneHandler::$securityFacade`<sup>[[?]](https://github.com/oroinc/crm/tree/2.2.0/src/Oro/Bundle/ContactBundle/Form/Handler/ContactPhoneHandler.php#L32 "Oro\Bundle\ContactBundle\Form\Handler\ContactPhoneHandler::$securityFacade")</sup> property was removed.
* The following properties in class `ContactListener`<sup>[[?]](https://github.com/oroinc/crm/tree/2.2.0/src/Oro/Bundle/ContactBundle/EventListener/ContactListener.php#L21 "Oro\Bundle\ContactBundle\EventListener\ContactListener")</sup> were removed:
   - `$container::$container`<sup>[[?]](https://github.com/oroinc/crm/tree/2.2.0/src/Oro/Bundle/ContactBundle/EventListener/ContactListener.php#L21 "Oro\Bundle\ContactBundle\EventListener\ContactListener::$container")</sup>
   - `$securityContext::$securityContext`<sup>[[?]](https://github.com/oroinc/crm/tree/2.2.0/src/Oro/Bundle/ContactBundle/EventListener/ContactListener.php#L26 "Oro\Bundle\ContactBundle\EventListener\ContactListener::$securityContext")</sup>
* The `ContactEmailApiHandler::__construct(OroEntityManager $entityManager, SecurityFacade $securityFacade)`<sup>[[?]](https://github.com/oroinc/crm/tree/2.2.0/src/Oro/Bundle/ContactBundle/Handler/ContactEmailApiHandler.php#L34 "Oro\Bundle\ContactBundle\Handler\ContactEmailApiHandler")</sup> method was changed to `ContactEmailApiHandler::__construct(EntityManager $entityManager, AuthorizationCheckerInterface $authorizationChecker)`<sup>[[?]](https://github.com/oroinc/crm/tree/2.3.0/src/Oro/Bundle/ContactBundle/Handler/ContactEmailApiHandler.php#L27 "Oro\Bundle\ContactBundle\Handler\ContactEmailApiHandler")</sup>
* The `ContactPhoneApiHandler::__construct(OroEntityManager $entityManager, SecurityFacade $securityFacade)`<sup>[[?]](https://github.com/oroinc/crm/tree/2.2.0/src/Oro/Bundle/ContactBundle/Handler/ContactPhoneApiHandler.php#L34 "Oro\Bundle\ContactBundle\Handler\ContactPhoneApiHandler")</sup> method was changed to `ContactPhoneApiHandler::__construct(EntityManager $entityManager, AuthorizationCheckerInterface $authorizationChecker)`<sup>[[?]](https://github.com/oroinc/crm/tree/2.3.0/src/Oro/Bundle/ContactBundle/Handler/ContactPhoneApiHandler.php#L27 "Oro\Bundle\ContactBundle\Handler\ContactPhoneApiHandler")</sup>
* The `ContactEmailHandler::__construct(FormInterface $form, Request $request, EntityManagerInterface $manager, ContactEmailDeleteValidator $contactEmailDeleteValidator, SecurityFacade $securityFacade)`<sup>[[?]](https://github.com/oroinc/crm/tree/2.2.0/src/Oro/Bundle/ContactBundle/Form/Handler/ContactEmailHandler.php#L41 "Oro\Bundle\ContactBundle\Form\Handler\ContactEmailHandler")</sup> method was changed to `ContactEmailHandler::__construct(FormInterface $form, Request $request, EntityManagerInterface $manager, ContactEmailDeleteValidator $contactEmailDeleteValidator, AuthorizationCheckerInterface $authorizationChecker)`<sup>[[?]](https://github.com/oroinc/crm/tree/2.3.0/src/Oro/Bundle/ContactBundle/Form/Handler/ContactEmailHandler.php#L41 "Oro\Bundle\ContactBundle\Form\Handler\ContactEmailHandler")</sup>
* The `ContactPhoneHandler::__construct(FormInterface $form, Request $request, EntityManagerInterface $manager, ContactPhoneDeleteValidator $contactPhoneDeleteValidator, SecurityFacade $securityFacade)`<sup>[[?]](https://github.com/oroinc/crm/tree/2.2.0/src/Oro/Bundle/ContactBundle/Form/Handler/ContactPhoneHandler.php#L41 "Oro\Bundle\ContactBundle\Form\Handler\ContactPhoneHandler")</sup> method was changed to `ContactPhoneHandler::__construct(FormInterface $form, Request $request, EntityManagerInterface $manager, ContactPhoneDeleteValidator $contactPhoneDeleteValidator, AuthorizationCheckerInterface $authorizationChecker)`<sup>[[?]](https://github.com/oroinc/crm/tree/2.3.0/src/Oro/Bundle/ContactBundle/Form/Handler/ContactPhoneHandler.php#L41 "Oro\Bundle\ContactBundle\Form\Handler\ContactPhoneHandler")</sup>
* The `ContactListener::__construct(ContainerInterface $container)`<sup>[[?]](https://github.com/oroinc/crm/tree/2.2.0/src/Oro/Bundle/ContactBundle/EventListener/ContactListener.php#L31 "Oro\Bundle\ContactBundle\EventListener\ContactListener")</sup> method was changed to `ContactListener::__construct(TokenStorageInterface $tokenStorage)`<sup>[[?]](https://github.com/oroinc/crm/tree/2.3.0/src/Oro/Bundle/ContactBundle/EventListener/ContactListener.php#L23 "Oro\Bundle\ContactBundle\EventListener\ContactListener")</sup>
* The `ContactListener::getSecurityContext`<sup>[[?]](https://github.com/oroinc/crm/tree/2.2.0/src/Oro/Bundle/ContactBundle/EventListener/ContactListener.php#L135 "Oro\Bundle\ContactBundle\EventListener\ContactListener::getSecurityContext")</sup> method was removed.

MagentoBundle
-------------
Support for data synchronization with Magento 2 by REST protocol was added. Store, website and regions dictionaries are available for synchronization. However, synchronization of other entities has not yet been developed and it is, therefore, not available in the current version of the package. This is the reason for Magento 2 integration being absent from the "Channel type" field when creating a new channel.

For more details on how to enable such integration, see [Magento 2 Documentation](src/Oro/Bundle/MagentoBundle/Resources/doc/reference/magento2.md).

* Interface `Oro\Bundle\MagentoBundle\Provider\Transport\MagentoSoapTransportInterface` was added.
    - public method `call($action, $params = [])` was added
* Interface `Oro\Bundle\MagentoBundle\Provider\Transport\MagentoTransportInterface`
    - removed the `call` method because it conflicts with REST conception. From now on, MagentoTransportInterface will not allow to specify http methods and resource through parameters.
    - public method `isCustomerHasUniqueEmail(Customer $customer)` was added
    - public method `getRequiredExtensionVersion()` was added
    - public method `initWithExtraOptions(Transport $transportEntity, array $clientExtraOptions)` was added
    - added methods `getCreditMemos()`, `getCreditMemoInfo($incrementId)`.
* Class `Oro\Bundle\MagentoBundle\Provider\ChannelType` was renamed to `Oro\Bundle\MagentoBundle\Provider\MagentoChannelType` and its service was renamed to `oro_magento.provider.magento_channel_type`
* Class `Oro\Bundle\MagentoBundle\Provider\Iterator\StoresSoapIterator` moved to `Oro\Bundle\MagentoBundle\Provider\Iterator\Soap\StoresSoapIterator`:
    - constant `ALL_WEBSITES` moved to `Oro\Bundle\MagentoBundle\Entity\Website`
    - constant `ADMIN_WEBSITE_ID` moved to `Oro\Bundle\MagentoBundle\Entity\Website`
    - constant `ADMIN_STORE_ID` moved to `Oro\Bundle\MagentoBundle\Entity\Store`
* Class `Oro\Bundle\MagentoBundle\Provider\Magento2ChannelType` was added to support Magento2 as a new integration
* Class `Oro\Bundle\MagentoBundle\Controller\IntegrationConfigController.php` was added. It is a universal entry point for both Magento and Magento2 integration check requests
* Class `Oro\Bundle\MagentoBundle\Entity\MagentoTransport` was added. It's a parent for `MagentoSoapTransport` and `MagentoRestTransport` and it has all their similar properties and methods
* Class `Oro\Bundle\MagentoBundle\Entity\MagentRestTransport` was added
* Class `Oro\Bundle\MagentoBundle\Entity\MagentoSoapTransport' was changed. Now it consists of fields and methods that are specific for SoapTransport.
* Class `Oro\Bundle\MagentoBundle\Entity\Repository\MagentoSoapTransportRepository` was renamed to `Oro\Bundle\MagentoBundle\Entity\Repository\MagentoTransportRepository`
* Class `Oro\Bundle\MagentoBundle\Form\EventListener\SoapConnectorsFormSubscriber` was renamed to `Oro\Bundle\MagentoBundle\Form\EventListener\ConnectorsFormSubscriber`
    - added protected method `getFormChannelType`
* Class `Oro\Bundle\MagentoBundle\Form\EventListener\SoapSettingsFormSubscriber` was renamed to `Oro\Bundle\MagentoBundle\Form\EventListener\SettingsFormSubscriber` and its service were renamed to `oro_magento.form.subscriber.transport_setting`
    - protected method `getModifierWebsitesList` was renamed to `modifyWebsitesList` and now it returns void.
* Abstract class `Oro\Bundle\MagentoBundle\Form\Type\AbstractTransportSettingFormType` was added. It is inherited by `SoapTransportSettingFormType` and `RestTransportSettingFormType`
* Class `Oro\Bundle\MagentoBundle\Form\Type\RestTransportSettingFormType` and its service `oro_magento.form.type.rest_transport_setting` were added
* Class `Oro\Bundle\MagentoBundle\Form\Type\SoapTransportCheckButtonType` was renamed to `Oro\Bundle\MagentoBundle\Form\Type\TransportCheckButtonType`
* Class `Oro\Bundle\MagentoBundle\Handler\TransportHandler` and its service `oro_magento.handler.transport` were added. It is a layer between transport and controller.
    - Method `getMagentoTransport` was added. Its main responsibility is to initialize and return MagentoTransport from check request.
    - Method `getCheckResponse`: returns array with data for response.
* Class `Oro\Bundle\MagentoBundle\ImportExport\Converter\Rest\StoreDataConverter` and its service `oro_magento.importexport.data_converter.rest.store` were added
* Class `Oro\Bundle\MagentoBundle\ImportExport\Converter\Rest\WebsiteDataConverter` and its service `oro_magento.importexport.data_converter.rest.website` were added
* Method `getSores` in `Oro\Bundle\MagentoBundle\Provider\CartExpirationProcessor` was renamed to `getStores`
* Class `Oro\Bundle\MagentoBundle\Provider\AbstractMagentoConnector` was moved to `Oro\Bundle\MagentoBundle\Connector\AbstractMagentoConnector`
* Class `Oro\Bundle\MagentoBundle\Provider\CartConnector` was moved to `Oro\Bundle\MagentoBundle\Connector\CartConnector`
* Class `Oro\Bundle\MagentoBundle\Provider\OrderConnector` was moved to `Oro\Bundle\MagentoBundle\Connector\OrderConnector`
* Class `Oro\Bundle\MagentoBundle\Provider\RegionConnector` was moved to `Oro\Bundle\MagentoBundle\Connector\RegionConnector`
* Class `Oro\Bundle\MagentoBundle\Provider\CustomerConnector` was moved to `Oro\Bundle\MagentoBundle\Connector\CustomerConnector`
* Class `Oro\Bundle\MagentoBundle\Provider\NewsletterSubscriberConnector` was moved to `Oro\Bundle\MagentoBundle\Connector\NewsletterSubscriberConnector`
* Class `Oro\Bundle\MagentoBundle\Provider\MagentoConnectorInterface` was moved to `Oro\Bundle\MagentoBundle\Connector\MagentoConnectorInterface`
* Class `Oro\Bundle\MagentoBundle\Provider\Connector\Rest\StoreConnector` and its service `oro_magento.mage.rest.store` were added
* Class `Oro\Bundle\MagentoBundle\Provider\Connector\Rest\WebsiteConnector` and its service `oro_magento.mage.rest.website` were added
* Class `Oro\Bundle\MagentoBundle\ProviderConnectorChoicesProvider` and its service `oro_magento.provider.connector_choices` were added. It has method:
    - `getAllowedConnectorsChoices` it returns a list of connectors available for some integration.
* Class `Oro\Bundle\MagentoBundle\Provider\Customer\CustomerIconProvider`. Its service was renamed to `oro_magento.provider.customer.magento_customer_icon`
    - construction signature was changed, now it takes the next arguments:
        - TypesRegistry $integrationTypeRegistry
        - CacheManager $cacheManager
* Class `Oro\Bundle\MagentoBundle\Provider\Iterator\AbstractLoadeableSoapIterator` was renamed to `Oro\Bundle\MagentoBundle\Provider\Iterator\AbstractLoadeableIterator`
    - protected property `transport` was removed
    - protected method `processCollectionResponse` was removed
* Class `Oro\Bundle\MagentoBundle\Provider\Iterator\AbstractPageableSoapIterator` was renamed to `Oro\Bundle\MagentoBundle\Provider\Iterator\AbstractPageableIterator`
    - construction signature was changed, now it takes the next arguments:
        - MagentoSoapTransportInterface $transport*
        - array $settings
    - protected method `processCollectionResponse` was removed
    - protected method `convertResponseToMultiArray` was removed
* Interface `Oro\Bundle\MagentoBundle\Provider\Iterator\NewsletterSubscriberBridgeIteratorInterface` was added
* Abstract class `Oro\Bundle\MagentoBundle\Provider\Iterator\Rest\AbstractLoadeableRestIterator` was added
* Class `Oro\Bundle\MagentoBundle\Provider\Iterator\Rest\BaseMagentoRestIterator` was added
* Class `Oro\Bundle\MagentoBundle\Provider\Iterator\Rest\StoresRestIterator` was added
* Class `Oro\Bundle\MagentoBundle\Provider\Iterator\Rest\WebsiteRestIterator` was added
* Class `Oro\Bundle\MagentoBundle\Provider\Iterator\AbstractBridgeIterator` was moved to `Oro\Bundle\MagentoBundle\Provider\Iterator\Soap\AbstractBridgeIterator`
    - construction signature was changed, now it takes the next arguments:
        - MagentoSoapTransportInterface $transport
        - array $settings
* Abstract class `Oro\Bundle\MagentoBundle\Provider\Iterator\Rest\AbstractLoadeableSoapIterator` was added
    - construction signature:
        - MagentoSoapTransportInterface $transport
    - method processCollectionResponse($response) was added
* Abstract class `Oro\Bundle\MagentoBundle\Provider\Iterator\Rest\AbstractPageableSoapIterator` was added
    - method `processCollectionResponse($response)` was added
    - method `convertResponseToMultiArray($response)` was added
    - method `applyWebsiteFilters(array $websiteIds, array $storeIds)` was added
* Class `Oro\Bundle\MagentoBundle\Provider\Iterator\CartsBridgeIterator` was moved to `Oro\Bundle\MagentoBundle\Provider\Iterator\Soap\CartsBridgeIterator`
* Class `Oro\Bundle\MagentoBundle\Provider\Iterator\CustomerBridgeIterator` was moved to `Oro\Bundle\MagentoBundle\Provider\Iterator\Soap\CustomerBridgeIterator`
* Class `Oro\Bundle\MagentoBundle\Provider\Iterator\CustomerSoapIterator` was moved to `Oro\Bundle\MagentoBundle\Provider\Iterator\Soap\CustomerSoapIterator`
* Class `Oro\Bundle\MagentoBundle\Provider\Iterator\CustomerGroupBridgeIterator` was moved to `Oro\Bundle\MagentoBundle\Provider\Iterator\Soap\CustomerGroupBridgeIterator`
* Class `Oro\Bundle\MagentoBundle\Provider\Iterator\NewsletterSubscriberBridgeIterator` was moved to `Oro\Bundle\MagentoBundle\Provider\Iterator\Soap\NewsletterSubscriberBridgeIterator` and now implements `NewsletterSubscriberBridgeIteratorInterface`
* Class `Oro\Bundle\MagentoBundle\Provider\Iterator\OrderBridgeIterator` was moved to `Oro\Bundle\MagentoBundle\Provider\Iterator\Soap\OrderBridgeIterator`
* Class `Oro\Bundle\MagentoBundle\Provider\Iterator\OrderSoapIterator` was moved to `Oro\Bundle\MagentoBundle\Provider\Iterator\Soap\OrderSoapIterator`
* Class `Oro\Bundle\MagentoBundle\Provider\Iterator\RegionSoapIterator` was moved to `Oro\Bundle\MagentoBundle\Provider\Iterator\Soap\RegionSoapIterator`
    - protected method `findEntitiesToProcess()` was moved to parent class
    - protected method `getEntityIds()` was moved to parent class
    - protected method `getEntity($id)` was moved to parent class
    - protected method `getIdFieldName()` was moved to parent class
    - protected method `current()` was moved to parent class
* Class `Oro\Bundle\MagentoBundle\Provider\Iterator\WebsiteSoapIterator` was moved to `Oro\Bundle\MagentoBundle\Provider\Iterator\Soap\WebsiteSoapIterator`
* Class `Oro\Bundle\MagentoBundle\Provider\Magento2ChannelType` and its service `oro_magento.provider.magento2_channel_type` were added
    - method getLabel() was added
    - method getIcon() was added
* Class `Oro\Bundle\MagentoBundle\Provider\RestPingProvider` and its service `oro_magento.provider.rest_ping_provider` were added. Use it to send ping request to Magento and store response data.
    - public method `setClient(RestClientInterface $client)` was added
    - public method `setHeaders(array $headers)` was added
    - public method `setParams(array $params)` was added
    - public method `isCustomerSharingPerWebsite()` was added
    - public method `getCustomerScope()` was added
    - public method `getMagentoVersion()` was added
    - public method `getBridgeVersion()` was added
    - public method `getAdminUrl()` was added
    - public method `isExtensionInstalled()` was added
    - public method `ping()` was added
    - public method `forceRequest()` was added
    - protected method `getClient()` was added
    - protected method `doRequest()` was added
    - protected method `processResponse(array $responseData)` was added
* Class `Oro\Bundle\MagentoBundle\Provider\RestRokenProvider` and its service `oro_magento.provider.rest_token_provider` were added. Use it to get a token, generate a new token and store it.
    - construction signature:
        - RegistryInterface $doctrine
        - Mcrypt $mcrypt
    - public method `getTokenFromEntity(MagentoTransport $transportEntity, RestClientInterface $client)` was added
    - public method `generateNewToken(MagentoTransport $transportEntity, RestClientInterface $client)` was added
    - protected method `doTokenRequest(RestClientInterface $client, array $params)` was added
    - protected method `validateStatusCodes(RestException $e)` was added
    - protected method `getTokenRequestParams(ParameterBag $parameterBag)` was added
    - protected method `updateToken(MagentoTransport $transportEntity, $token)` was added
* Class `Oro\Bundle\MagentoBundle\Provider\Transport\RestTransportAdapter` was added. It converts MagentoRestTransport entity to interface suitable for REST client factory.
* Class `Oro\Bundle\MagentoBundle\Provider\Transport\RestTransport` and its service `oro_magento.transport.rest_transport` were added. Implements `TransportInterface`, `MagentoTransportInterface`, `ServerTimeAwareInterface`, `PingableInterface`, `LoggerAwareInterface`
    - const `REGION_RESPONSE_TYPE` was added
    - construction signature takes the next arguments:
        - RestClientFactoryInterface $clientFactory
        - RestTokenProvider $restTokenProvider
        - RestPingProvider $pingProvider
        - ResponseConverterManager $responseConverterManager
This class has the same responsibilities as SoapTransport.
* Class `Oro\Bundle\MagentoBundle\Provider\Transport\SoapTransport` now implements `TransportCacheClearInterface`
    - Updated according to `Oro\Bundle\MagentoBundle\Provider\Transport\MagentoTransportInterface` changes.
    - construction signature was changed, now it takes the next arguments:
        - Mcrypt $encoder
        - WsdlManager $wsdlManager
        - UniqueCustomerEmailSoapProvider $uniqueCustomerEmailProvider
        - array $bundleConfig = []
    - public method `isCustomerHasUniqueEmail(Customer $customer)` was added
    - public method `getRequiredExtensionVersion()` was added
    - public method `cacheClear($resource = null)` was added
    - public method `getCreditMemos()` was added
    - public method `getCreditMemoInfo($incrementId)` was added
* Class `Oro\Bundle\MagentoBundle\Provider\TransportEntityProvider` and its service `oro_magento.provider.transport_entity` were added
    - construction signature:
        - FormFactoryInterface $formFactory
        - ManagerRegistry $registry
    - public method `getTransportEntityByRequest(MagentoTransportInterface $transport, Request $request)` was added
    - protected method `findTransportEntity(TransportInterface $settingsEntity, $entityId)` was added
* Class `Oro\Bundle\MagentoBundle\Provider\UniqueCustomerEmailSoapProvider` and its service `oro_magento.provider.soap.unique_customer_email` were added
    - public method `isCustomerHasUniqueEmail(MagentoSoapTransportInterface $transport, Customer $customer)` was added
    - protected method `doRequest(MagentoSoapTransportInterface $transport, array $filters)` was added
    - protected method `getPreparedFilters(Customer $customer)` was added
* Class `Oro\Bundle\MagentoBundle\Provider\WebsiteChoicesProvider` and its service `oro_magento.provider.website_choices` were added
    - construction signature:
        - TranslatorInterface $translator
    - public method `formatWebsiteChoices(MagentoTransportInterface $transport)` was added
* The next batch jobs were added to `batch_jobs.yml`:
    - mage_store_rest_import
    - mage_website_rest_import
* New channel `magento2` was added to `channels.yml`
* Route `oro_magento_soap_check` was renamed to `oro_magento_integration_check`
* Translation with key `not_valid_parameters` was removed
* Class `Oro\Bundle\MagentoBundle\Validator\UniqueCustomerEmailValidator` was changed
    - construction signature was changed, now it takes the next arguments:
        - TypesRegistry $typesRegistry
* Interface `Oro\Bundle\MagentoBundle\Converter\RestResponseConverterInterface` was added
    - public method `convert($data)` was added
* Class `Oro\Bundle\MagentoBundle\Converter\Rest\RegionConverter` with its service `oro_magento.converter.rest.region_converter` were added. Implements `RestResponseConverterInterface`
* Class `Oro\Bundle\MagentoBundle\Converter\Rest\ResponseConverterManager` with its service `oro_magento.converter.rest.response_converter_manager` were added
    - public method `convert($data, $type)` was added
    - public method `addConverter($responseType, RestResponseConverterInterface $converter)` was added
* Class `Oro\Bundle\MagentoBundle\DependencyInjection\Compiler\ResponseConvertersPass` was added. Collects converters that implement `RestResponseConverterInterface`
* Abstract class `Oro\Bundle\MagentoBundle\Provider\Iterator\AbstractRegionIterator` was added
    - abstract protected method `getCountryList()` was added
* Abstract class `Oro\Bundle\MagentoBundle\Provider\Iterator\RegionRestIterator` was added. Extends `AbstractRegionIterator` with REST implementation
* Process `magento_schedule_integration` was removed. Two new processes `magento_soap_schedule_integration` and `magento_rest_schedule_integration` were added
* Class `Oro\Bundle\MagentoBundle\Entity\Order`
    - field `originId` added
    - `Oro\Bundle\MagentoBundle\Entity\OriginTrait` used
* Class `Oro\Bundle\MagentoBundle\Autocomplete\IntegrationAwareSearchHandler`
    - method `setSecurityFacade` was replaced with `setAuthorizationChecker`
* Class `Oro\Bundle\MagentoBundle\Datagrid\NewsletterSubscriberPermissionProvider`
    - method `setSecurityFacade` was replaced with `setAuthorizationChecker`
* Class EventDispatchableRestClientFactory was added. It extends the basic factory functionality with an event which can be used to decorate REST client or replace it.
* Interface Oro/Bundle/IntegrationBundle/Provider/Rest/Client/FactoryInterface was added.
* Interface Oro/Bundle/IntegrationBundle/Provider/Rest/Transport/RestTransportSettingsInterface was added. The purpose of RestTransportSettingsInterface interface is to provide settings required for REST client initialization and are used in factories.
* Event Oro\Bundle\IntegrationBundle\Event\ClientCreatedAfterEvent was added.  It is an event which is called when a new client is created. Use it if you want to decorate or replace a client in case of irregular behavior.
* Class Oro\Bundle\IntegrationBundle\EventListener\AbstractClientDecoratorListener was added. It is used by Oro\Bundle\IntegrationBundle\EventListener\LoggerClientDecoratorListener and Oro\Bundle\IntegrationBundle\EventListener\MultiAttemptsClientDecoratorListener. These listeners decorate the client entity after it was created.
* Trait Oro\Bundle\IntegrationBundle\Utils\MultiAttemptsConfigTrait was added. It is used in Oro/Bundle/MagentoBundle/Provider/Transport/SoapTransport and Oro\Bundle\IntegrationBundle\EventListener\MultiAttemptsClientDecoratorListener to execute the feature several times, if the process fails after the first try.
* Class `Oro\Bundle\MagentoBundle\Service\AutomaticDiscovery`
    - changed the constructor signature: parameter `OwnershipMetadataProvider $ownershipMetadata` was replaced with `OwnershipMetadataProviderInterface $ownershipMetadata`

* The following classes were removed:
   - `AbstractMagentoConnector`<sup>[[?]](https://github.com/oroinc/crm/tree/2.2.0/src/Oro/Bundle/MagentoBundle/Provider/AbstractMagentoConnector.php#L21 "Oro\Bundle\MagentoBundle\Provider\AbstractMagentoConnector")</sup>
   - `CartConnector`<sup>[[?]](https://github.com/oroinc/crm/tree/2.2.0/src/Oro/Bundle/MagentoBundle/Provider/CartConnector.php#L5 "Oro\Bundle\MagentoBundle\Provider\CartConnector")</sup>
   - `CustomerConnector`<sup>[[?]](https://github.com/oroinc/crm/tree/2.2.0/src/Oro/Bundle/MagentoBundle/Provider/CustomerConnector.php#L7 "Oro\Bundle\MagentoBundle\Provider\CustomerConnector")</sup>
   - `NewsletterSubscriberConnector`<sup>[[?]](https://github.com/oroinc/crm/tree/2.2.0/src/Oro/Bundle/MagentoBundle/Provider/NewsletterSubscriberConnector.php#L5 "Oro\Bundle\MagentoBundle\Provider\NewsletterSubscriberConnector")</sup>
   - `OrderConnector`<sup>[[?]](https://github.com/oroinc/crm/tree/2.2.0/src/Oro/Bundle/MagentoBundle/Provider/OrderConnector.php#L5 "Oro\Bundle\MagentoBundle\Provider\OrderConnector")</sup>
   - `RegionConnector`<sup>[[?]](https://github.com/oroinc/crm/tree/2.2.0/src/Oro/Bundle/MagentoBundle/Provider/RegionConnector.php#L7 "Oro\Bundle\MagentoBundle\Provider\RegionConnector")</sup>
   - `AbstractBridgeIterator`<sup>[[?]](https://github.com/oroinc/crm/tree/2.2.0/src/Oro/Bundle/MagentoBundle/Provider/Iterator/AbstractBridgeIterator.php#L9 "Oro\Bundle\MagentoBundle\Provider\Iterator\AbstractBridgeIterator")</sup>
   - `CartsBridgeIterator`<sup>[[?]](https://github.com/oroinc/crm/tree/2.2.0/src/Oro/Bundle/MagentoBundle/Provider/Iterator/CartsBridgeIterator.php#L8 "Oro\Bundle\MagentoBundle\Provider\Iterator\CartsBridgeIterator")</sup>
   - `CustomerBridgeIterator`<sup>[[?]](https://github.com/oroinc/crm/tree/2.2.0/src/Oro/Bundle/MagentoBundle/Provider/Iterator/CustomerBridgeIterator.php#L8 "Oro\Bundle\MagentoBundle\Provider\Iterator\CustomerBridgeIterator")</sup>
   - `CustomerGroupSoapIterator`<sup>[[?]](https://github.com/oroinc/crm/tree/2.2.0/src/Oro/Bundle/MagentoBundle/Provider/Iterator/CustomerGroupSoapIterator.php#L7 "Oro\Bundle\MagentoBundle\Provider\Iterator\CustomerGroupSoapIterator")</sup>
   - `CustomerSoapIterator`<sup>[[?]](https://github.com/oroinc/crm/tree/2.2.0/src/Oro/Bundle/MagentoBundle/Provider/Iterator/CustomerSoapIterator.php#L9 "Oro\Bundle\MagentoBundle\Provider\Iterator\CustomerSoapIterator")</sup>
   - `NewsletterSubscriberBridgeIterator`<sup>[[?]](https://github.com/oroinc/crm/tree/2.2.0/src/Oro/Bundle/MagentoBundle/Provider/Iterator/NewsletterSubscriberBridgeIterator.php#L10 "Oro\Bundle\MagentoBundle\Provider\Iterator\NewsletterSubscriberBridgeIterator")</sup>
   - `OrderBridgeIterator`<sup>[[?]](https://github.com/oroinc/crm/tree/2.2.0/src/Oro/Bundle/MagentoBundle/Provider/Iterator/OrderBridgeIterator.php#L7 "Oro\Bundle\MagentoBundle\Provider\Iterator\OrderBridgeIterator")</sup>
   - `OrderSoapIterator`<sup>[[?]](https://github.com/oroinc/crm/tree/2.2.0/src/Oro/Bundle/MagentoBundle/Provider/Iterator/OrderSoapIterator.php#L9 "Oro\Bundle\MagentoBundle\Provider\Iterator\OrderSoapIterator")</sup>
   - `RegionSoapIterator`<sup>[[?]](https://github.com/oroinc/crm/tree/2.2.0/src/Oro/Bundle/MagentoBundle/Provider/Iterator/RegionSoapIterator.php#L7 "Oro\Bundle\MagentoBundle\Provider\Iterator\RegionSoapIterator")</sup>
   - `StoresSoapIterator`<sup>[[?]](https://github.com/oroinc/crm/tree/2.2.0/src/Oro/Bundle/MagentoBundle/Provider/Iterator/StoresSoapIterator.php#L7 "Oro\Bundle\MagentoBundle\Provider\Iterator\StoresSoapIterator")</sup>
   - `WebsiteSoapIterator`<sup>[[?]](https://github.com/oroinc/crm/tree/2.2.0/src/Oro/Bundle/MagentoBundle/Provider/Iterator/WebsiteSoapIterator.php#L8 "Oro\Bundle\MagentoBundle\Provider\Iterator\WebsiteSoapIterator")</sup>
   - `SoapController`<sup>[[?]](https://github.com/oroinc/crm/tree/2.2.0/src/Oro/Bundle/MagentoBundle/Controller/SoapController.php#L22 "Oro\Bundle\MagentoBundle\Controller\SoapController")</sup>. Use `Oro\Bundle\MagentoBundle\Controller\IntegrationConfigController.php`instead
* The `UniqueCustomerEmailValidator::getRemoteCustomers`<sup>[[?]](https://github.com/oroinc/crm/tree/2.2.0/src/Oro/Bundle/MagentoBundle/Validator/UniqueCustomerEmailValidator.php#L66 "Oro\Bundle\MagentoBundle\Validator\UniqueCustomerEmailValidator::getRemoteCustomers")</sup> method was removed.
* The `CustomerController::getSecurityFacade`<sup>[[?]](https://github.com/oroinc/crm/tree/2.2.0/src/Oro/Bundle/MagentoBundle/Controller/CustomerController.php#L228 "Oro\Bundle\MagentoBundle\Controller\CustomerController::getSecurityFacade")</sup> method was removed.
* The `UniqueCustomerEmailValidator::__construct(MagentoTransportInterface $transport)`<sup>[[?]](https://github.com/oroinc/crm/tree/2.2.0/src/Oro/Bundle/MagentoBundle/Validator/UniqueCustomerEmailValidator.php#L24 "Oro\Bundle\MagentoBundle\Validator\UniqueCustomerEmailValidator")</sup> method was changed to `UniqueCustomerEmailValidator::__construct(TypesRegistry $typesRegistry)`<sup>[[?]](https://github.com/oroinc/crm/tree/2.3.0/src/Oro/Bundle/MagentoBundle/Validator/UniqueCustomerEmailValidator.php#L24 "Oro\Bundle\MagentoBundle\Validator\UniqueCustomerEmailValidator")</sup>
* The `AutomaticDiscovery::__construct(DoctrineHelper $doctrineHelper, DiscoveryStrategyInterface $defaultStrategy, OwnershipMetadataProvider $ownershipMetadata, $discoveryEntityClass, array $configuration)`<sup>[[?]](https://github.com/oroinc/crm/tree/2.2.0/src/Oro/Bundle/MagentoBundle/Service/AutomaticDiscovery.php#L58 "Oro\Bundle\MagentoBundle\Service\AutomaticDiscovery")</sup> method was changed to `AutomaticDiscovery::__construct(DoctrineHelper $doctrineHelper, DiscoveryStrategyInterface $defaultStrategy, OwnershipMetadataProviderInterface $ownershipMetadata, $discoveryEntityClass, array $configuration)`<sup>[[?]](https://github.com/oroinc/crm/tree/2.3.0/src/Oro/Bundle/MagentoBundle/Service/AutomaticDiscovery.php#L58 "Oro\Bundle\MagentoBundle\Service\AutomaticDiscovery")</sup>
* The `SoapTransport::__construct(Mcrypt $encoder, WsdlManager $wsdlManager, array $bundleConfig = [])`<sup>[[?]](https://github.com/oroinc/crm/tree/2.2.0/src/Oro/Bundle/MagentoBundle/Provider/Transport/SoapTransport.php#L121 "Oro\Bundle\MagentoBundle\Provider\Transport\SoapTransport")</sup> method was changed to `SoapTransport::__construct(Mcrypt $encoder, WsdlManager $wsdlManager, UniqueCustomerEmailSoapProvider $uniqueCustomerEmailProvider, array $bundleConfig = [])`<sup>[[?]](https://github.com/oroinc/crm/tree/2.3.0/src/Oro/Bundle/MagentoBundle/Provider/Transport/SoapTransport.php#L142 "Oro\Bundle\MagentoBundle\Provider\Transport\SoapTransport")</sup>
* The `CustomerIconProvider::__construct(ChannelType $channelType, CacheManager $cacheManager)`<sup>[[?]](https://github.com/oroinc/crm/tree/2.2.0/src/Oro/Bundle/MagentoBundle/Provider/Customer/CustomerIconProvider.php#L22 "Oro\Bundle\MagentoBundle\Provider\Customer\CustomerIconProvider")</sup> method was changed to `CustomerIconProvider::__construct(TypesRegistry $integrationTypeRegistry, CacheManager $cacheManager)`<sup>[[?]](https://github.com/oroinc/crm/tree/2.3.0/src/Oro/Bundle/MagentoBundle/Provider/Customer/CustomerIconProvider.php#L24 "Oro\Bundle\MagentoBundle\Provider\Customer\CustomerIconProvider")</sup>
* The `CustomerGroupSelectType::__construct(SecurityFacade $securityFacade)`<sup>[[?]](https://github.com/oroinc/crm/tree/2.2.0/src/Oro/Bundle/MagentoBundle/Form/Type/CustomerGroupSelectType.php#L28 "Oro\Bundle\MagentoBundle\Form\Type\CustomerGroupSelectType")</sup> method was changed to `CustomerGroupSelectType::__construct(AuthorizationCheckerInterface $authorizationChecker)`<sup>[[?]](https://github.com/oroinc/crm/tree/2.3.0/src/Oro/Bundle/MagentoBundle/Form/Type/CustomerGroupSelectType.php#L23 "Oro\Bundle\MagentoBundle\Form\Type\CustomerGroupSelectType")</sup>
* The `CartAddressHandler::__construct(FormInterface $form, Request $request, RegistryInterface $registry, SecurityContextInterface $security)`<sup>[[?]](https://github.com/oroinc/crm/tree/2.2.0/src/Oro/Bundle/MagentoBundle/Form/Handler/CartAddressHandler.php#L48 "Oro\Bundle\MagentoBundle\Form\Handler\CartAddressHandler")</sup> method was changed to `CartAddressHandler::__construct(FormInterface $form, Request $request, RegistryInterface $registry, TokenAccessorInterface $security)`<sup>[[?]](https://github.com/oroinc/crm/tree/2.3.0/src/Oro/Bundle/MagentoBundle/Form/Handler/CartAddressHandler.php#L48 "Oro\Bundle\MagentoBundle\Form\Handler\CartAddressHandler")</sup>
* The `CartHandler::__construct(FormInterface $form, Request $request, RegistryInterface $registry, SecurityContextInterface $security)`<sup>[[?]](https://github.com/oroinc/crm/tree/2.2.0/src/Oro/Bundle/MagentoBundle/Form/Handler/CartHandler.php#L31 "Oro\Bundle\MagentoBundle\Form\Handler\CartHandler")</sup> method was changed to `CartHandler::__construct(FormInterface $form, Request $request, RegistryInterface $registry, TokenAccessorInterface $security)`<sup>[[?]](https://github.com/oroinc/crm/tree/2.3.0/src/Oro/Bundle/MagentoBundle/Form/Handler/CartHandler.php#L31 "Oro\Bundle\MagentoBundle\Form\Handler\CartHandler")</sup>
* The `CustomerAddressApiHandler::__construct(FormInterface $form, Request $request, RegistryInterface $registry, SecurityContextInterface $security)`<sup>[[?]](https://github.com/oroinc/crm/tree/2.2.0/src/Oro/Bundle/MagentoBundle/Form/Handler/CustomerAddressApiHandler.php#L35 "Oro\Bundle\MagentoBundle\Form\Handler\CustomerAddressApiHandler")</sup> method was changed to `CustomerAddressApiHandler::__construct(FormInterface $form, Request $request, RegistryInterface $registry, TokenAccessorInterface $security)`<sup>[[?]](https://github.com/oroinc/crm/tree/2.3.0/src/Oro/Bundle/MagentoBundle/Form/Handler/CustomerAddressApiHandler.php#L35 "Oro\Bundle\MagentoBundle\Form\Handler\CustomerAddressApiHandler")</sup>
* The `CustomerApiHandler::__construct(FormInterface $form, Request $request, RegistryInterface $registry, SecurityContextInterface $security, AccountCustomerManager $accountCustomerManager)`<sup>[[?]](https://github.com/oroinc/crm/tree/2.2.0/src/Oro/Bundle/MagentoBundle/Form/Handler/CustomerApiHandler.php#L41 "Oro\Bundle\MagentoBundle\Form\Handler\CustomerApiHandler")</sup> method was changed to `CustomerApiHandler::__construct(FormInterface $form, Request $request, RegistryInterface $registry, TokenAccessorInterface $security, AccountCustomerManager $accountCustomerManager)`<sup>[[?]](https://github.com/oroinc/crm/tree/2.3.0/src/Oro/Bundle/MagentoBundle/Form/Handler/CustomerApiHandler.php#L40 "Oro\Bundle\MagentoBundle\Form\Handler\CustomerApiHandler")</sup>
* The `OrderAddressApiHandler::__construct(FormInterface $form, Request $request, ObjectManager $entityManager, SecurityContextInterface $security)`<sup>[[?]](https://github.com/oroinc/crm/tree/2.2.0/src/Oro/Bundle/MagentoBundle/Form/Handler/OrderAddressApiHandler.php#L25 "Oro\Bundle\MagentoBundle\Form\Handler\OrderAddressApiHandler")</sup> method was changed to `OrderAddressApiHandler::__construct(FormInterface $form, Request $request, ObjectManager $entityManager, TokenAccessorInterface $security)`<sup>[[?]](https://github.com/oroinc/crm/tree/2.3.0/src/Oro/Bundle/MagentoBundle/Form/Handler/OrderAddressApiHandler.php#L25 "Oro\Bundle\MagentoBundle\Form\Handler\OrderAddressApiHandler")</sup>
* The `OrderHandler::__construct(FormInterface $form, Request $request, RegistryInterface $registry, SecurityContextInterface $security)`<sup>[[?]](https://github.com/oroinc/crm/tree/2.2.0/src/Oro/Bundle/MagentoBundle/Form/Handler/OrderHandler.php#L35 "Oro\Bundle\MagentoBundle\Form\Handler\OrderHandler")</sup> method was changed to `OrderHandler::__construct(FormInterface $form, Request $request, RegistryInterface $registry, TokenAccessorInterface $security)`<sup>[[?]](https://github.com/oroinc/crm/tree/2.3.0/src/Oro/Bundle/MagentoBundle/Form/Handler/OrderHandler.php#L35 "Oro\Bundle\MagentoBundle\Form\Handler\OrderHandler")</sup>
* The `CustomerGroupGridListener::__construct(SecurityFacade $securityFacade)`<sup>[[?]](https://github.com/oroinc/crm/tree/2.2.0/src/Oro/Bundle/MagentoBundle/EventListener/CustomerGroupGridListener.php#L22 "Oro\Bundle\MagentoBundle\EventListener\CustomerGroupGridListener")</sup> method was changed to `CustomerGroupGridListener::__construct(AuthorizationCheckerInterface $authorizationChecker)`<sup>[[?]](https://github.com/oroinc/crm/tree/2.3.0/src/Oro/Bundle/MagentoBundle/EventListener/CustomerGroupGridListener.php#L23 "Oro\Bundle\MagentoBundle\EventListener\CustomerGroupGridListener")</sup>
* The `StoreGridListener::__construct(SecurityFacade $securityFacade, $dataChannelClass, EntityManager $entityManager)`<sup>[[?]](https://github.com/oroinc/crm/tree/2.2.0/src/Oro/Bundle/MagentoBundle/EventListener/StoreGridListener.php#L35 "Oro\Bundle\MagentoBundle\EventListener\StoreGridListener")</sup> method was changed to `StoreGridListener::__construct(AuthorizationCheckerInterface $authorizationChecker, $dataChannelClass, EntityManager $entityManager)`<sup>[[?]](https://github.com/oroinc/crm/tree/2.3.0/src/Oro/Bundle/MagentoBundle/EventListener/StoreGridListener.php#L36 "Oro\Bundle\MagentoBundle\EventListener\StoreGridListener")</sup>
* The `UniqueCustomerEmailValidator::$transport`<sup>[[?]](https://github.com/oroinc/crm/tree/2.2.0/src/Oro/Bundle/MagentoBundle/Validator/UniqueCustomerEmailValidator.php#L19 "Oro\Bundle\MagentoBundle\Validator\UniqueCustomerEmailValidator::$transport")</sup> property was removed.
* The `CustomerIconProvider::$channelType`<sup>[[?]](https://github.com/oroinc/crm/tree/2.2.0/src/Oro/Bundle/MagentoBundle/Provider/Customer/CustomerIconProvider.php#L14 "Oro\Bundle\MagentoBundle\Provider\Customer\CustomerIconProvider::$channelType")</sup> property was removed.
* The `CustomerGroupSelectType::$securityFacade`<sup>[[?]](https://github.com/oroinc/crm/tree/2.2.0/src/Oro/Bundle/MagentoBundle/Form/Type/CustomerGroupSelectType.php#L18 "Oro\Bundle\MagentoBundle\Form\Type\CustomerGroupSelectType::$securityFacade")</sup> property was removed.
* The following properties in class `SoapTransportSettingFormType`<sup>[[?]](https://github.com/oroinc/crm/tree/2.2.0/src/Oro/Bundle/MagentoBundle/Form/Type/SoapTransportSettingFormType.php#L21 "Oro\Bundle\MagentoBundle\Form\Type\SoapTransportSettingFormType")</sup> were removed:
   - `$transport::$transport`<sup>[[?]](https://github.com/oroinc/crm/tree/2.2.0/src/Oro/Bundle/MagentoBundle/Form/Type/SoapTransportSettingFormType.php#L21 "Oro\Bundle\MagentoBundle\Form\Type\SoapTransportSettingFormType::$transport")</sup>
   - `$subscriber::$subscriber`<sup>[[?]](https://github.com/oroinc/crm/tree/2.2.0/src/Oro/Bundle/MagentoBundle/Form/Type/SoapTransportSettingFormType.php#L24 "Oro\Bundle\MagentoBundle\Form\Type\SoapTransportSettingFormType::$subscriber")</sup>
   - `$registry::$registry`<sup>[[?]](https://github.com/oroinc/crm/tree/2.2.0/src/Oro/Bundle/MagentoBundle/Form/Type/SoapTransportSettingFormType.php#L27 "Oro\Bundle\MagentoBundle\Form\Type\SoapTransportSettingFormType::$registry")</sup>
* The `CustomerGroupGridListener::$securityFacade`<sup>[[?]](https://github.com/oroinc/crm/tree/2.2.0/src/Oro/Bundle/MagentoBundle/EventListener/CustomerGroupGridListener.php#L17 "Oro\Bundle\MagentoBundle\EventListener\CustomerGroupGridListener::$securityFacade")</sup> property was removed.
* The `StoreGridListener::$securityFacade`<sup>[[?]](https://github.com/oroinc/crm/tree/2.2.0/src/Oro/Bundle/MagentoBundle/EventListener/StoreGridListener.php#L22 "Oro\Bundle\MagentoBundle\EventListener\StoreGridListener::$securityFacade")</sup> property was removed.
* The following properties in class `MagentoSoapTransport`<sup>[[?]](https://github.com/oroinc/crm/tree/2.2.0/src/Oro/Bundle/MagentoBundle/Entity/MagentoSoapTransport.php#L25 "Oro\Bundle\MagentoBundle\Entity\MagentoSoapTransport")</sup> were removed:
   - `$wsdlUrl::$wsdlUrl`<sup>[[?]](https://github.com/oroinc/crm/tree/2.2.0/src/Oro/Bundle/MagentoBundle/Entity/MagentoSoapTransport.php#L25 "Oro\Bundle\MagentoBundle\Entity\MagentoSoapTransport::$wsdlUrl")</sup>
   - `$apiUser::$apiUser`<sup>[[?]](https://github.com/oroinc/crm/tree/2.2.0/src/Oro/Bundle/MagentoBundle/Entity/MagentoSoapTransport.php#L32 "Oro\Bundle\MagentoBundle\Entity\MagentoSoapTransport::$apiUser")</sup>
   - `$apiKey::$apiKey`<sup>[[?]](https://github.com/oroinc/crm/tree/2.2.0/src/Oro/Bundle/MagentoBundle/Entity/MagentoSoapTransport.php#L39 "Oro\Bundle\MagentoBundle\Entity\MagentoSoapTransport::$apiKey")</sup>
   - `$syncStartDate::$syncStartDate`<sup>[[?]](https://github.com/oroinc/crm/tree/2.2.0/src/Oro/Bundle/MagentoBundle/Entity/MagentoSoapTransport.php#L46 "Oro\Bundle\MagentoBundle\Entity\MagentoSoapTransport::$syncStartDate")</sup>
   - `$initialSyncStartDate::$initialSyncStartDate`<sup>[[?]](https://github.com/oroinc/crm/tree/2.2.0/src/Oro/Bundle/MagentoBundle/Entity/MagentoSoapTransport.php#L53 "Oro\Bundle\MagentoBundle\Entity\MagentoSoapTransport::$initialSyncStartDate")</sup>
   - `$syncRange::$syncRange`<sup>[[?]](https://github.com/oroinc/crm/tree/2.2.0/src/Oro/Bundle/MagentoBundle/Entity/MagentoSoapTransport.php#L60 "Oro\Bundle\MagentoBundle\Entity\MagentoSoapTransport::$syncRange")</sup>
   - `$websiteId::$websiteId`<sup>[[?]](https://github.com/oroinc/crm/tree/2.2.0/src/Oro/Bundle/MagentoBundle/Entity/MagentoSoapTransport.php#L67 "Oro\Bundle\MagentoBundle\Entity\MagentoSoapTransport::$websiteId")</sup>
   - `$websites::$websites`<sup>[[?]](https://github.com/oroinc/crm/tree/2.2.0/src/Oro/Bundle/MagentoBundle/Entity/MagentoSoapTransport.php#L74 "Oro\Bundle\MagentoBundle\Entity\MagentoSoapTransport::$websites")</sup>
   - `$isExtensionInstalled::$isExtensionInstalled`<sup>[[?]](https://github.com/oroinc/crm/tree/2.2.0/src/Oro/Bundle/MagentoBundle/Entity/MagentoSoapTransport.php#L81 "Oro\Bundle\MagentoBundle\Entity\MagentoSoapTransport::$isExtensionInstalled")</sup>
   - `$extensionVersion::$extensionVersion`<sup>[[?]](https://github.com/oroinc/crm/tree/2.2.0/src/Oro/Bundle/MagentoBundle/Entity/MagentoSoapTransport.php#L88 "Oro\Bundle\MagentoBundle\Entity\MagentoSoapTransport::$extensionVersion")</sup>
   - `$magentoVersion::$magentoVersion`<sup>[[?]](https://github.com/oroinc/crm/tree/2.2.0/src/Oro/Bundle/MagentoBundle/Entity/MagentoSoapTransport.php#L95 "Oro\Bundle\MagentoBundle\Entity\MagentoSoapTransport::$magentoVersion")</sup>
   - `$guestCustomerSync::$guestCustomerSync`<sup>[[?]](https://github.com/oroinc/crm/tree/2.2.0/src/Oro/Bundle/MagentoBundle/Entity/MagentoSoapTransport.php#L109 "Oro\Bundle\MagentoBundle\Entity\MagentoSoapTransport::$guestCustomerSync")</sup>
   - `$adminUrl::$adminUrl`<sup>[[?]](https://github.com/oroinc/crm/tree/2.2.0/src/Oro/Bundle/MagentoBundle/Entity/MagentoSoapTransport.php#L116 "Oro\Bundle\MagentoBundle\Entity\MagentoSoapTransport::$adminUrl")</sup>
   - `$newsletterSubscriberSyncedToId::$newsletterSubscriberSyncedToId`<sup>[[?]](https://github.com/oroinc/crm/tree/2.2.0/src/Oro/Bundle/MagentoBundle/Entity/MagentoSoapTransport.php#L123 "Oro\Bundle\MagentoBundle\Entity\MagentoSoapTransport::$newsletterSubscriberSyncedToId")</sup>
   - `$settings::$settings`<sup>[[?]](https://github.com/oroinc/crm/tree/2.2.0/src/Oro/Bundle/MagentoBundle/Entity/MagentoSoapTransport.php#L133 "Oro\Bundle\MagentoBundle\Entity\MagentoSoapTransport::$settings")</sup>
* The `NewsletterSubscriberPermissionProvider::$securityFacade`<sup>[[?]](https://github.com/oroinc/crm/tree/2.2.0/src/Oro/Bundle/MagentoBundle/Datagrid/NewsletterSubscriberPermissionProvider.php#L15 "Oro\Bundle\MagentoBundle\Datagrid\NewsletterSubscriberPermissionProvider::$securityFacade")</sup> property was removed.
* The `IntegrationAwareSearchHandler::$securityFacade`<sup>[[?]](https://github.com/oroinc/crm/tree/2.2.0/src/Oro/Bundle/MagentoBundle/Autocomplete/IntegrationAwareSearchHandler.php#L15 "Oro\Bundle\MagentoBundle\Autocomplete\IntegrationAwareSearchHandler::$securityFacade")</sup> property was removed.
* The following methods in class `SoapTransportSettingFormType`<sup>[[?]](https://github.com/oroinc/crm/tree/2.2.0/src/Oro/Bundle/MagentoBundle/Form/Type/SoapTransportSettingFormType.php#L34 "Oro\Bundle\MagentoBundle\Form\Type\SoapTransportSettingFormType")</sup> were removed:
   - `__construct::__construct`<sup>[[?]](https://github.com/oroinc/crm/tree/2.2.0/src/Oro/Bundle/MagentoBundle/Form/Type/SoapTransportSettingFormType.php#L34 "Oro\Bundle\MagentoBundle\Form\Type\SoapTransportSettingFormType::__construct")</sup>
   - `setDefaultOptions::setDefaultOptions`<sup>[[?]](https://github.com/oroinc/crm/tree/2.2.0/src/Oro/Bundle/MagentoBundle/Form/Type/SoapTransportSettingFormType.php#L132 "Oro\Bundle\MagentoBundle\Form\Type\SoapTransportSettingFormType::setDefaultOptions")</sup>
* The following methods in class `MagentoSoapTransport`<sup>[[?]](https://github.com/oroinc/crm/tree/2.2.0/src/Oro/Bundle/MagentoBundle/Entity/MagentoSoapTransport.php#L135 "Oro\Bundle\MagentoBundle\Entity\MagentoSoapTransport")</sup> were removed:
   - `__construct::__construct`<sup>[[?]](https://github.com/oroinc/crm/tree/2.2.0/src/Oro/Bundle/MagentoBundle/Entity/MagentoSoapTransport.php#L135 "Oro\Bundle\MagentoBundle\Entity\MagentoSoapTransport::__construct")</sup>
   - `setWsdlUrl::setWsdlUrl`<sup>[[?]](https://github.com/oroinc/crm/tree/2.2.0/src/Oro/Bundle/MagentoBundle/Entity/MagentoSoapTransport.php#L145 "Oro\Bundle\MagentoBundle\Entity\MagentoSoapTransport::setWsdlUrl")</sup>
   - `getWsdlUrl::getWsdlUrl`<sup>[[?]](https://github.com/oroinc/crm/tree/2.2.0/src/Oro/Bundle/MagentoBundle/Entity/MagentoSoapTransport.php#L155 "Oro\Bundle\MagentoBundle\Entity\MagentoSoapTransport::getWsdlUrl")</sup>
   - `setApiUser::setApiUser`<sup>[[?]](https://github.com/oroinc/crm/tree/2.2.0/src/Oro/Bundle/MagentoBundle/Entity/MagentoSoapTransport.php#L165 "Oro\Bundle\MagentoBundle\Entity\MagentoSoapTransport::setApiUser")</sup>
   - `getApiUser::getApiUser`<sup>[[?]](https://github.com/oroinc/crm/tree/2.2.0/src/Oro/Bundle/MagentoBundle/Entity/MagentoSoapTransport.php#L175 "Oro\Bundle\MagentoBundle\Entity\MagentoSoapTransport::getApiUser")</sup>
   - `setApiKey::setApiKey`<sup>[[?]](https://github.com/oroinc/crm/tree/2.2.0/src/Oro/Bundle/MagentoBundle/Entity/MagentoSoapTransport.php#L185 "Oro\Bundle\MagentoBundle\Entity\MagentoSoapTransport::setApiKey")</sup>
   - `getApiKey::getApiKey`<sup>[[?]](https://github.com/oroinc/crm/tree/2.2.0/src/Oro/Bundle/MagentoBundle/Entity/MagentoSoapTransport.php#L195 "Oro\Bundle\MagentoBundle\Entity\MagentoSoapTransport::getApiKey")</sup>
   - `setSyncStartDate::setSyncStartDate`<sup>[[?]](https://github.com/oroinc/crm/tree/2.2.0/src/Oro/Bundle/MagentoBundle/Entity/MagentoSoapTransport.php#L205 "Oro\Bundle\MagentoBundle\Entity\MagentoSoapTransport::setSyncStartDate")</sup>
   - `getSyncStartDate::getSyncStartDate`<sup>[[?]](https://github.com/oroinc/crm/tree/2.2.0/src/Oro/Bundle/MagentoBundle/Entity/MagentoSoapTransport.php#L215 "Oro\Bundle\MagentoBundle\Entity\MagentoSoapTransport::getSyncStartDate")</sup>
   - `setSyncRange::setSyncRange`<sup>[[?]](https://github.com/oroinc/crm/tree/2.2.0/src/Oro/Bundle/MagentoBundle/Entity/MagentoSoapTransport.php#L225 "Oro\Bundle\MagentoBundle\Entity\MagentoSoapTransport::setSyncRange")</sup>
   - `getSyncRange::getSyncRange`<sup>[[?]](https://github.com/oroinc/crm/tree/2.2.0/src/Oro/Bundle/MagentoBundle/Entity/MagentoSoapTransport.php#L235 "Oro\Bundle\MagentoBundle\Entity\MagentoSoapTransport::getSyncRange")</sup>
   - `setWebsiteId::setWebsiteId`<sup>[[?]](https://github.com/oroinc/crm/tree/2.2.0/src/Oro/Bundle/MagentoBundle/Entity/MagentoSoapTransport.php#L245 "Oro\Bundle\MagentoBundle\Entity\MagentoSoapTransport::setWebsiteId")</sup>
   - `getWebsiteId::getWebsiteId`<sup>[[?]](https://github.com/oroinc/crm/tree/2.2.0/src/Oro/Bundle/MagentoBundle/Entity/MagentoSoapTransport.php#L255 "Oro\Bundle\MagentoBundle\Entity\MagentoSoapTransport::getWebsiteId")</sup>
   - `setWebsites::setWebsites`<sup>[[?]](https://github.com/oroinc/crm/tree/2.2.0/src/Oro/Bundle/MagentoBundle/Entity/MagentoSoapTransport.php#L265 "Oro\Bundle\MagentoBundle\Entity\MagentoSoapTransport::setWebsites")</sup>
   - `getWebsites::getWebsites`<sup>[[?]](https://github.com/oroinc/crm/tree/2.2.0/src/Oro/Bundle/MagentoBundle/Entity/MagentoSoapTransport.php#L275 "Oro\Bundle\MagentoBundle\Entity\MagentoSoapTransport::getWebsites")</sup>
   - `setIsExtensionInstalled::setIsExtensionInstalled`<sup>[[?]](https://github.com/oroinc/crm/tree/2.2.0/src/Oro/Bundle/MagentoBundle/Entity/MagentoSoapTransport.php#L285 "Oro\Bundle\MagentoBundle\Entity\MagentoSoapTransport::setIsExtensionInstalled")</sup>
   - `getIsExtensionInstalled::getIsExtensionInstalled`<sup>[[?]](https://github.com/oroinc/crm/tree/2.2.0/src/Oro/Bundle/MagentoBundle/Entity/MagentoSoapTransport.php#L295 "Oro\Bundle\MagentoBundle\Entity\MagentoSoapTransport::getIsExtensionInstalled")</sup>
   - `getExtensionVersion::getExtensionVersion`<sup>[[?]](https://github.com/oroinc/crm/tree/2.2.0/src/Oro/Bundle/MagentoBundle/Entity/MagentoSoapTransport.php#L303 "Oro\Bundle\MagentoBundle\Entity\MagentoSoapTransport::getExtensionVersion")</sup>
   - `setExtensionVersion::setExtensionVersion`<sup>[[?]](https://github.com/oroinc/crm/tree/2.2.0/src/Oro/Bundle/MagentoBundle/Entity/MagentoSoapTransport.php#L312 "Oro\Bundle\MagentoBundle\Entity\MagentoSoapTransport::setExtensionVersion")</sup>
   - `isSupportedExtensionVersion::isSupportedExtensionVersion`<sup>[[?]](https://github.com/oroinc/crm/tree/2.2.0/src/Oro/Bundle/MagentoBundle/Entity/MagentoSoapTransport.php#L322 "Oro\Bundle\MagentoBundle\Entity\MagentoSoapTransport::isSupportedExtensionVersion")</sup>
   - `getMagentoVersion::getMagentoVersion`<sup>[[?]](https://github.com/oroinc/crm/tree/2.2.0/src/Oro/Bundle/MagentoBundle/Entity/MagentoSoapTransport.php#L331 "Oro\Bundle\MagentoBundle\Entity\MagentoSoapTransport::getMagentoVersion")</sup>
   - `setMagentoVersion::setMagentoVersion`<sup>[[?]](https://github.com/oroinc/crm/tree/2.2.0/src/Oro/Bundle/MagentoBundle/Entity/MagentoSoapTransport.php#L340 "Oro\Bundle\MagentoBundle\Entity\MagentoSoapTransport::setMagentoVersion")</sup>
   - `setGuestCustomerSync::setGuestCustomerSync`<sup>[[?]](https://github.com/oroinc/crm/tree/2.2.0/src/Oro/Bundle/MagentoBundle/Entity/MagentoSoapTransport.php#L372 "Oro\Bundle\MagentoBundle\Entity\MagentoSoapTransport::setGuestCustomerSync")</sup>
   - `getGuestCustomerSync::getGuestCustomerSync`<sup>[[?]](https://github.com/oroinc/crm/tree/2.2.0/src/Oro/Bundle/MagentoBundle/Entity/MagentoSoapTransport.php#L382 "Oro\Bundle\MagentoBundle\Entity\MagentoSoapTransport::getGuestCustomerSync")</sup>
   - `setAdminUrl::setAdminUrl`<sup>[[?]](https://github.com/oroinc/crm/tree/2.2.0/src/Oro/Bundle/MagentoBundle/Entity/MagentoSoapTransport.php#L419 "Oro\Bundle\MagentoBundle\Entity\MagentoSoapTransport::setAdminUrl")</sup>
   - `getAdminUrl::getAdminUrl`<sup>[[?]](https://github.com/oroinc/crm/tree/2.2.0/src/Oro/Bundle/MagentoBundle/Entity/MagentoSoapTransport.php#L429 "Oro\Bundle\MagentoBundle\Entity\MagentoSoapTransport::getAdminUrl")</sup>
   - `getInitialSyncStartDate::getInitialSyncStartDate`<sup>[[?]](https://github.com/oroinc/crm/tree/2.2.0/src/Oro/Bundle/MagentoBundle/Entity/MagentoSoapTransport.php#L437 "Oro\Bundle\MagentoBundle\Entity\MagentoSoapTransport::getInitialSyncStartDate")</sup>
   - `setInitialSyncStartDate::setInitialSyncStartDate`<sup>[[?]](https://github.com/oroinc/crm/tree/2.2.0/src/Oro/Bundle/MagentoBundle/Entity/MagentoSoapTransport.php#L446 "Oro\Bundle\MagentoBundle\Entity\MagentoSoapTransport::setInitialSyncStartDate")</sup>
   - `getNewsletterSubscriberSyncedToId::getNewsletterSubscriberSyncedToId`<sup>[[?]](https://github.com/oroinc/crm/tree/2.2.0/src/Oro/Bundle/MagentoBundle/Entity/MagentoSoapTransport.php#L480 "Oro\Bundle\MagentoBundle\Entity\MagentoSoapTransport::getNewsletterSubscriberSyncedToId")</sup>
   - `setNewsletterSubscriberSyncedToId::setNewsletterSubscriberSyncedToId`<sup>[[?]](https://github.com/oroinc/crm/tree/2.2.0/src/Oro/Bundle/MagentoBundle/Entity/MagentoSoapTransport.php#L489 "Oro\Bundle\MagentoBundle\Entity\MagentoSoapTransport::setNewsletterSubscriberSyncedToId")</sup>
* The `NewsletterSubscriberPermissionProvider::setSecurityFacade`<sup>[[?]](https://github.com/oroinc/crm/tree/2.2.0/src/Oro/Bundle/MagentoBundle/Datagrid/NewsletterSubscriberPermissionProvider.php#L65 "Oro\Bundle\MagentoBundle\Datagrid\NewsletterSubscriberPermissionProvider::setSecurityFacade")</sup> method was removed.
* The `IntegrationAwareSearchHandler::setSecurityFacade`<sup>[[?]](https://github.com/oroinc/crm/tree/2.2.0/src/Oro/Bundle/MagentoBundle/Autocomplete/IntegrationAwareSearchHandler.php#L31 "Oro\Bundle\MagentoBundle\Autocomplete\IntegrationAwareSearchHandler::setSecurityFacade")</sup> method was removed.

MarketingCRM
------------
* The following methods in class `AbstractPrecalculatedVisitProvider`<sup>[[?]](https://github.com/oroinc/crm/tree/2.2.0/src/Oro/Bridge/MarketingCRM/Provider/AbstractPrecalculatedVisitProvider.php#L69 "Oro\Bridge\MarketingCRM\Provider\AbstractPrecalculatedVisitProvider")</sup> were removed:
   - `applyDateLimitWithOptionalDates::applyDateLimitWithOptionalDates`<sup>[[?]](https://github.com/oroinc/crm/tree/2.2.0/src/Oro/Bridge/MarketingCRM/Provider/AbstractPrecalculatedVisitProvider.php#L69 "Oro\Bridge\MarketingCRM\Provider\AbstractPrecalculatedVisitProvider::applyDateLimitWithOptionalDates")</sup>
   - `applyDateLimitFrom::applyDateLimitFrom`<sup>[[?]](https://github.com/oroinc/crm/tree/2.2.0/src/Oro/Bridge/MarketingCRM/Provider/AbstractPrecalculatedVisitProvider.php#L89 "Oro\Bridge\MarketingCRM\Provider\AbstractPrecalculatedVisitProvider::applyDateLimitFrom")</sup>
   - `applyDateLimitTo::applyDateLimitTo`<sup>[[?]](https://github.com/oroinc/crm/tree/2.2.0/src/Oro/Bridge/MarketingCRM/Provider/AbstractPrecalculatedVisitProvider.php#L99 "Oro\Bridge\MarketingCRM\Provider\AbstractPrecalculatedVisitProvider::applyDateLimitTo")</sup>

SalesBundle
-----------
* The `LeadMailboxProcessProvider::__construct(Registry $registry, ServiceLink $securityLink)`<sup>[[?]](https://github.com/oroinc/crm/tree/2.2.0/src/Oro/Bundle/SalesBundle/Provider/LeadMailboxProcessProvider.php#L28 "Oro\Bundle\SalesBundle\Provider\LeadMailboxProcessProvider")</sup> method was changed to `LeadMailboxProcessProvider::__construct(Registry $registry)`<sup>[[?]](https://github.com/oroinc/crm/tree/2.3.0/src/Oro/Bundle/SalesBundle/Provider/LeadMailboxProcessProvider.php#L22 "Oro\Bundle\SalesBundle\Provider\LeadMailboxProcessProvider")</sup>
* The `B2bCustomerEmailApiHandler::__construct(OroEntityManager $entityManager, SecurityFacade $securityFacade)`<sup>[[?]](https://github.com/oroinc/crm/tree/2.2.0/src/Oro/Bundle/SalesBundle/Handler/B2bCustomerEmailApiHandler.php#L34 "Oro\Bundle\SalesBundle\Handler\B2bCustomerEmailApiHandler")</sup> method was changed to `B2bCustomerEmailApiHandler::__construct(EntityManager $entityManager, AuthorizationCheckerInterface $authorizationChecker)`<sup>[[?]](https://github.com/oroinc/crm/tree/2.3.0/src/Oro/Bundle/SalesBundle/Handler/B2bCustomerEmailApiHandler.php#L27 "Oro\Bundle\SalesBundle\Handler\B2bCustomerEmailApiHandler")</sup>
* The `B2bCustomerPhoneApiHandler::__construct(OroEntityManager $entityManager, SecurityFacade $securityFacade)`<sup>[[?]](https://github.com/oroinc/crm/tree/2.2.0/src/Oro/Bundle/SalesBundle/Handler/B2bCustomerPhoneApiHandler.php#L34 "Oro\Bundle\SalesBundle\Handler\B2bCustomerPhoneApiHandler")</sup> method was changed to `B2bCustomerPhoneApiHandler::__construct(EntityManager $entityManager, AuthorizationCheckerInterface $authorizationChecker)`<sup>[[?]](https://github.com/oroinc/crm/tree/2.3.0/src/Oro/Bundle/SalesBundle/Handler/B2bCustomerPhoneApiHandler.php#L27 "Oro\Bundle\SalesBundle\Handler\B2bCustomerPhoneApiHandler")</sup>
* The `LeadPhoneApiHandler::__construct(Registry $doctrine, SecurityFacade $securityFacade)`<sup>[[?]](https://github.com/oroinc/crm/tree/2.2.0/src/Oro/Bundle/SalesBundle/Handler/LeadPhoneApiHandler.php#L35 "Oro\Bundle\SalesBundle\Handler\LeadPhoneApiHandler")</sup> method was changed to `LeadPhoneApiHandler::__construct(ManagerRegistry $doctrine, AuthorizationCheckerInterface $authorizationChecker)`<sup>[[?]](https://github.com/oroinc/crm/tree/2.3.0/src/Oro/Bundle/SalesBundle/Handler/LeadPhoneApiHandler.php#L27 "Oro\Bundle\SalesBundle\Handler\LeadPhoneApiHandler")</sup>
* The `CustomerType::__construct(DataTransformerInterface $transformer, ConfigProvider $customerConfigProvider, EntityAliasResolver $entityAliasResolver, CustomerIconProviderInterface $customerIconProvider, TranslatorInterface $translator, SecurityFacade $securityFacade, ManagerInterface $gridManager, EntityNameResolver $entityNameResolver, MultiGridProvider $multiGridProvider)`<sup>[[?]](https://github.com/oroinc/crm/tree/2.2.0/src/Oro/Bundle/SalesBundle/Form/Type/CustomerType.php#L66 "Oro\Bundle\SalesBundle\Form\Type\CustomerType")</sup> method was changed to `CustomerType::__construct(DataTransformerInterface $transformer, ConfigProvider $customerConfigProvider, EntityAliasResolver $entityAliasResolver, CustomerIconProviderInterface $customerIconProvider, TranslatorInterface $translator, AuthorizationCheckerInterface $authorizationChecker, ManagerInterface $gridManager, EntityNameResolver $entityNameResolver, MultiGridProvider $multiGridProvider)`<sup>[[?]](https://github.com/oroinc/crm/tree/2.3.0/src/Oro/Bundle/SalesBundle/Form/Type/CustomerType.php#L66 "Oro\Bundle\SalesBundle\Form\Type\CustomerType")</sup>
* The `B2bCustomerEmailHandler::__construct(FormInterface $form, Request $request, EntityManagerInterface $manager, B2bCustomerEmailDeleteValidator $b2bCustomerEmailDeleteValidator, SecurityFacade $securityFacade)`<sup>[[?]](https://github.com/oroinc/crm/tree/2.2.0/src/Oro/Bundle/SalesBundle/Form/Handler/B2bCustomerEmailHandler.php#L41 "Oro\Bundle\SalesBundle\Form\Handler\B2bCustomerEmailHandler")</sup> method was changed to `B2bCustomerEmailHandler::__construct(FormInterface $form, Request $request, EntityManagerInterface $manager, B2bCustomerEmailDeleteValidator $b2bCustomerEmailDeleteValidator, AuthorizationCheckerInterface $authorizationChecker)`<sup>[[?]](https://github.com/oroinc/crm/tree/2.3.0/src/Oro/Bundle/SalesBundle/Form/Handler/B2bCustomerEmailHandler.php#L41 "Oro\Bundle\SalesBundle\Form\Handler\B2bCustomerEmailHandler")</sup>
* The `B2bCustomerPhoneHandler::__construct(FormInterface $form, Request $request, EntityManagerInterface $manager, B2bCustomerPhoneDeleteValidator $b2bCustomerPhoneDeleteValidator, SecurityFacade $securityFacade)`<sup>[[?]](https://github.com/oroinc/crm/tree/2.2.0/src/Oro/Bundle/SalesBundle/Form/Handler/B2bCustomerPhoneHandler.php#L41 "Oro\Bundle\SalesBundle\Form\Handler\B2bCustomerPhoneHandler")</sup> method was changed to `B2bCustomerPhoneHandler::__construct(FormInterface $form, Request $request, EntityManagerInterface $manager, B2bCustomerPhoneDeleteValidator $b2bCustomerPhoneDeleteValidator, AuthorizationCheckerInterface $authorizationChecker)`<sup>[[?]](https://github.com/oroinc/crm/tree/2.3.0/src/Oro/Bundle/SalesBundle/Form/Handler/B2bCustomerPhoneHandler.php#L41 "Oro\Bundle\SalesBundle\Form\Handler\B2bCustomerPhoneHandler")</sup>
* The `LeadEmailHandler::__construct(FormFactory $form, Request $request, EntityManagerInterface $manager, SecurityFacade $securityFacade)`<sup>[[?]](https://github.com/oroinc/crm/tree/2.2.0/src/Oro/Bundle/SalesBundle/Form/Handler/LeadEmailHandler.php#L36 "Oro\Bundle\SalesBundle\Form\Handler\LeadEmailHandler")</sup> method was changed to `LeadEmailHandler::__construct(FormFactory $form, Request $request, EntityManagerInterface $manager, AuthorizationCheckerInterface $authorizationChecker)`<sup>[[?]](https://github.com/oroinc/crm/tree/2.3.0/src/Oro/Bundle/SalesBundle/Form/Handler/LeadEmailHandler.php#L36 "Oro\Bundle\SalesBundle\Form\Handler\LeadEmailHandler")</sup>
* The `LeadPhoneHandler::__construct(FormFactory $form, Request $request, EntityManagerInterface $manager, SecurityFacade $securityFacade)`<sup>[[?]](https://github.com/oroinc/crm/tree/2.2.0/src/Oro/Bundle/SalesBundle/Form/Handler/LeadPhoneHandler.php#L36 "Oro\Bundle\SalesBundle\Form\Handler\LeadPhoneHandler")</sup> method was changed to `LeadPhoneHandler::__construct(FormFactory $form, Request $request, EntityManagerInterface $manager, AuthorizationCheckerInterface $authorizationChecker)`<sup>[[?]](https://github.com/oroinc/crm/tree/2.3.0/src/Oro/Bundle/SalesBundle/Form/Handler/LeadPhoneHandler.php#L36 "Oro\Bundle\SalesBundle\Form\Handler\LeadPhoneHandler")</sup>
* The `B2bCustomerEmailApiHandler::$securityFacade`<sup>[[?]](https://github.com/oroinc/crm/tree/2.2.0/src/Oro/Bundle/SalesBundle/Handler/B2bCustomerEmailApiHandler.php#L28 "Oro\Bundle\SalesBundle\Handler\B2bCustomerEmailApiHandler::$securityFacade")</sup> property was removed.
* The `B2bCustomerPhoneApiHandler::$securityFacade`<sup>[[?]](https://github.com/oroinc/crm/tree/2.2.0/src/Oro/Bundle/SalesBundle/Handler/B2bCustomerPhoneApiHandler.php#L28 "Oro\Bundle\SalesBundle\Handler\B2bCustomerPhoneApiHandler::$securityFacade")</sup> property was removed.
* The `LeadPhoneApiHandler::$securityFacade`<sup>[[?]](https://github.com/oroinc/crm/tree/2.2.0/src/Oro/Bundle/SalesBundle/Handler/LeadPhoneApiHandler.php#L29 "Oro\Bundle\SalesBundle\Handler\LeadPhoneApiHandler::$securityFacade")</sup> property was removed.
* The `CustomerType::$securityFacade`<sup>[[?]](https://github.com/oroinc/crm/tree/2.2.0/src/Oro/Bundle/SalesBundle/Form/Type/CustomerType.php#L44 "Oro\Bundle\SalesBundle\Form\Type\CustomerType::$securityFacade")</sup> property was removed.
* The `B2bCustomerEmailHandler::$securityFacade`<sup>[[?]](https://github.com/oroinc/crm/tree/2.2.0/src/Oro/Bundle/SalesBundle/Form/Handler/B2bCustomerEmailHandler.php#L32 "Oro\Bundle\SalesBundle\Form\Handler\B2bCustomerEmailHandler::$securityFacade")</sup> property was removed.
* The `B2bCustomerPhoneHandler::$securityFacade`<sup>[[?]](https://github.com/oroinc/crm/tree/2.2.0/src/Oro/Bundle/SalesBundle/Form/Handler/B2bCustomerPhoneHandler.php#L32 "Oro\Bundle\SalesBundle\Form\Handler\B2bCustomerPhoneHandler::$securityFacade")</sup> property was removed.
* The `LeadEmailHandler::$securityFacade`<sup>[[?]](https://github.com/oroinc/crm/tree/2.2.0/src/Oro/Bundle/SalesBundle/Form/Handler/LeadEmailHandler.php#L28 "Oro\Bundle\SalesBundle\Form\Handler\LeadEmailHandler::$securityFacade")</sup> property was removed.
* The `LeadPhoneHandler::$securityFacade`<sup>[[?]](https://github.com/oroinc/crm/tree/2.2.0/src/Oro/Bundle/SalesBundle/Form/Handler/LeadPhoneHandler.php#L28 "Oro\Bundle\SalesBundle\Form\Handler\LeadPhoneHandler::$securityFacade")</sup> property was removed.

