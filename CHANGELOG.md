## 2.4.0 (Unreleased)
[Show detailed list of changes](#file-incompatibilities-2-4-0.md)

## 2.3.4 (2017-09-05)
## 2.3.3 (2017-08-29)
## 2.3.2 (2017-08-22)
## 2.3.1 (2017-08-16)
## 2.3.0 (2017-07-28)
[Show detailed list of changes](#file-incompatibilities-2-3-0.md)

### Added
* **MagentoBundle**: Interface `Oro\Bundle\MagentoBundle\Provider\Transport\MagentoSoapTransportInterface` was added.
    * public method `call($action, $params = [])` was added
* **MagentoBundle**: Interface `Oro\Bundle\MagentoBundle\Provider\Transport\MagentoTransportInterface`
    * public method `isCustomerHasUniqueEmail(Customer $customer)` was added
    * public method `getRequiredExtensionVersion()` was added
    * public method `initWithExtraOptions(Transport $transportEntity, array $clientExtraOptions)` was added
    * added methods `getCreditMemos()`, `getCreditMemoInfo($incrementId)`.
* **MagentoBundle**: Class `Oro\Bundle\MagentoBundle\Provider\Magento2ChannelType` was added to support Magento2 as a new integration
* **MagentoBundle**: Class `Oro\Bundle\MagentoBundle\Controller\IntegrationConfigController.php` was added. It is a universal entry point for both Magento and Magento2 integration check requests
* **MagentoBundle**: Class `Oro\Bundle\MagentoBundle\Entity\MagentoTransport` was added. It's a parent for `MagentoSoapTransport` and `MagentoRestTransport` and it has all their similar properties and methods
* **MagentoBundle**: Class `Oro\Bundle\MagentoBundle\Entity\MagentRestTransport` was added
* **MagentoBundle**: Abstract class `Oro\Bundle\MagentoBundle\Form\Type\AbstractTransportSettingFormType` was added. It is inherited by `SoapTransportSettingFormType` and `RestTransportSettingFormType`
* **MagentoBundle**: Class `Oro\Bundle\MagentoBundle\Form\Type\RestTransportSettingFormType` and its service `oro_magento.form.type.rest_transport_setting` were added
* **MagentoBundle**: Class `Oro\Bundle\MagentoBundle\Handler\TransportHandler` and its service `oro_magento.handler.transport` were added. It is a layer between transport and controller.
    * Method `getMagentoTransport` was added. Its main responsibility is to initialize and return MagentoTransport from check request.
    * Method `getCheckResponse`: returns array with data for response.
* **MagentoBundle**: Class `Oro\Bundle\MagentoBundle\ImportExport\Converter\Rest\StoreDataConverter` and its service `oro_magento.importexport.data_converter.rest.store` were added
* **MagentoBundle**: Class `Oro\Bundle\MagentoBundle\ImportExport\Converter\Rest\WebsiteDataConverter` and its service `oro_magento.importexport.data_converter.rest.website` were added
* **MagentoBundle**: Class `Oro\Bundle\MagentoBundle\Provider\Connector\Rest\StoreConnector` and its service `oro_magento.mage.rest.store` were added
* **MagentoBundle**: Class `Oro\Bundle\MagentoBundle\Provider\Connector\Rest\WebsiteConnector` and its service `oro_magento.mage.rest.website` were added
* **MagentoBundle**: Class `Oro\Bundle\MagentoBundle\ProviderConnectorChoicesProvider` and its service `oro_magento.provider.connector_choices` were added. It has method:
    * `getAllowedConnectorsChoices` it returns a list of connectors available for some integration.
* **MagentoBundle**: Abstract class `Oro\Bundle\MagentoBundle\Provider\Iterator\Rest\AbstractLoadeableRestIterator` was added
* **MagentoBundle**: Class `Oro\Bundle\MagentoBundle\Provider\Iterator\Rest\BaseMagentoRestIterator` was added
* **MagentoBundle**: Class `Oro\Bundle\MagentoBundle\Provider\Iterator\Rest\StoresRestIterator` was added
* **MagentoBundle**: Class `Oro\Bundle\MagentoBundle\Provider\Iterator\Rest\WebsiteRestIterator` was added
* **MagentoBundle**: Abstract class `Oro\Bundle\MagentoBundle\Provider\Iterator\Rest\AbstractLoadeableSoapIterator` was added
    * method processCollectionResponse($response) was added
* **MagentoBundle**: Abstract class `Oro\Bundle\MagentoBundle\Provider\Iterator\Rest\AbstractPageableSoapIterator` was added
    * method `processCollectionResponse($response)` was added
    * method `convertResponseToMultiArray($response)` was added
    * method `applyWebsiteFilters(array $websiteIds, array $storeIds)` was added
* **MagentoBundle**: Class `Oro\Bundle\MagentoBundle\Provider\Magento2ChannelType` and its service `oro_magento.provider.magento2_channel_type` were added
    * method getLabel() was added
    * method getIcon() was added
* **MagentoBundle**: Class `Oro\Bundle\MagentoBundle\Provider\RestPingProvider` and its service `oro_magento.provider.rest_ping_provider` were added. Use it to send ping request to Magento and store response data.
    * public method `setClient(RestClientInterface $client)` was added
    * public method `setHeaders(array $headers)` was added
    * public method `setParams(array $params)` was added
    * public method `isCustomerSharingPerWebsite()` was added
    * public method `getCustomerScope()` was added
    * public method `getMagentoVersion()` was added
    * public method `getBridgeVersion()` was added
    * public method `getAdminUrl()` was added
    * public method `isExtensionInstalled()` was added
    * public method `ping()` was added
    * public method `forceRequest()` was added
    * protected method `getClient()` was added
    * protected method `doRequest()` was added
    * protected method `processResponse(array $responseData)` was added
* **MagentoBundle**: Class `Oro\Bundle\MagentoBundle\Provider\RestRokenProvider` and its service `oro_magento.provider.rest_token_provider` were added. Use it to get a token, generate a new token and store it.
    * public method `getTokenFromEntity(MagentoTransport $transportEntity, RestClientInterface $client)` was added
    * public method `generateNewToken(MagentoTransport $transportEntity, RestClientInterface $client)` was added
    * protected method `doTokenRequest(RestClientInterface $client, array $params)` was added
    * protected method `validateStatusCodes(RestException $e)` was added
    * protected method `getTokenRequestParams(ParameterBag $parameterBag)` was added
    * protected method `updateToken(MagentoTransport $transportEntity, $token)` was added
* **MagentoBundle**: Class `Oro\Bundle\MagentoBundle\Provider\Transport\RestTransportAdapter` was added. It converts MagentoRestTransport entity to interface suitable for REST client factory.
* **MagentoBundle**: Class `Oro\Bundle\MagentoBundle\Provider\Transport\RestTransport` and its service `oro_magento.transport.rest_transport` were added. Implements `TransportInterface`, `MagentoTransportInterface`, `ServerTimeAwareInterface`, `PingableInterface`, `LoggerAwareInterface`
This class has the same responsibilities as SoapTransport.
* **MagentoBundle**: Class `Oro\Bundle\MagentoBundle\Provider\TransportEntityProvider` and its service `oro_magento.provider.transport_entity` were added
    * public method `getTransportEntityByRequest(MagentoTransportInterface $transport, Request $request)` was added
    * protected method `findTransportEntity(TransportInterface $settingsEntity, $entityId)` was added
* **MagentoBundle**: Class `Oro\Bundle\MagentoBundle\Provider\UniqueCustomerEmailSoapProvider` and its service `oro_magento.provider.soap.unique_customer_email` were added
    - public method `isCustomerHasUniqueEmail(MagentoSoapTransportInterface $transport, Customer $customer)` was added
    - protected method `doRequest(MagentoSoapTransportInterface $transport, array $filters)` was added
    - protected method `getPreparedFilters(Customer $customer)` was added
* **MagentoBundle**: Class `Oro\Bundle\MagentoBundle\Provider\WebsiteChoicesProvider` and its service `oro_magento.provider.website_choices` were added
    - public method `formatWebsiteChoices(MagentoTransportInterface $transport)` was added
* **MagentoBundle**: The next batch jobs were added to `batch_jobs.yml`:
    - mage_store_rest_import
    - mage_website_rest_import
* **MagentoBundle**: New channel `magento2` was added to `channels.yml`
* **MagentoBundle**: Interface `Oro\Bundle\MagentoBundle\Converter\RestResponseConverterInterface` was added
    * public method `convert($data)` was added
* **MagentoBundle**: Class `Oro\Bundle\MagentoBundle\Converter\Rest\RegionConverter` with its service `oro_magento.converter.rest.region_converter` were added. Implements `RestResponseConverterInterface`
* **MagentoBundle**: Class `Oro\Bundle\MagentoBundle\Converter\Rest\ResponseConverterManager` with its service `oro_magento.converter.rest.response_converter_manager` were added
    * public method `convert($data, $type)` was added
    * public method `addConverter($responseType, RestResponseConverterInterface $converter)` was added
* **MagentoBundle**: Class `Oro\Bundle\MagentoBundle\DependencyInjection\Compiler\ResponseConvertersPass` was added. Collects converters that implement `RestResponseConverterInterface`
* **MagentoBundle**: Abstract class `Oro\Bundle\MagentoBundle\Provider\Iterator\AbstractRegionIterator` was added
    * abstract protected method `getCountryList()` was added
* **MagentoBundle**: Abstract class `Oro\Bundle\MagentoBundle\Provider\Iterator\RegionRestIterator` was added. Extends `AbstractRegionIterator` with REST implementation
* **MagentoBundle**: Processes `magento_soap_schedule_integration` and `magento_rest_schedule_integration` were added
* **MagentoBundle**: Class EventDispatchableRestClientFactory was added. It extends the basic factory functionality with an event which can be used to decorate REST client or replace it.
* **MagentoBundle**: Interface Oro/Bundle/IntegrationBundle/Provider/Rest/Client/FactoryInterface was added.
* **MagentoBundle**: Interface Oro/Bundle/IntegrationBundle/Provider/Rest/Transport/RestTransportSettingsInterface was added. The purpose of RestTransportSettingsInterface interface is to provide settings required for REST client initialization and are used in factories.
* **MagentoBundle**: Event Oro\Bundle\IntegrationBundle\Event\ClientCreatedAfterEvent was added.  It is an event which is called when a new client is created. Use it if you want to decorate or replace a client in case of irregular behavior.
* **MagentoBundle**: Class Oro\Bundle\IntegrationBundle\EventListener\AbstractClientDecoratorListener was added. It is used by Oro\Bundle\IntegrationBundle\EventListener\LoggerClientDecoratorListener and Oro\Bundle\IntegrationBundle\EventListener\MultiAttemptsClientDecoratorListener. These listeners decorate the client entity after it was created.
* **MagentoBundle**: Trait Oro\Bundle\IntegrationBundle\Utils\MultiAttemptsConfigTrait was added. It is used in Oro/Bundle/MagentoBundle/Provider/Transport/SoapTransport and Oro\Bundle\IntegrationBundle\EventListener\MultiAttemptsClientDecoratorListener to execute the feature several times, if the process fails after the first try.

### Changed
* **MagentoBundle**: Support for data synchronization with Magento 2 by REST protocol was added. Store, website and regions dictionaries are available for synchronization. However, synchronization of other entities has not yet been developed and it is, therefore, not available in the current version of the package. This is the reason for Magento 2 integration being absent from the "Channel type" field when creating a new channel.
For more details on how to enable such integration, see [Magento 2 Documentation](src/Oro/Bundle/MagentoBundle/Resources/doc/reference/magento2.md).

* **MagentoBundle**: Class `Oro\Bundle\MagentoBundle\Provider\ChannelType` was renamed to `Oro\Bundle\MagentoBundle\Provider\MagentoChannelType` and its service was renamed to `oro_magento.provider.magento_channel_type`
* **MagentoBundle**: Class `Oro\Bundle\MagentoBundle\Provider\Iterator\StoresSoapIterator` moved to `Oro\Bundle\MagentoBundle\Provider\Iterator\Soap\StoresSoapIterator`:
    * constant `ALL_WEBSITES` moved to `Oro\Bundle\MagentoBundle\Entity\Website`
    * constant `ADMIN_WEBSITE_ID` moved to `Oro\Bundle\MagentoBundle\Entity\Website`
    * constant `ADMIN_STORE_ID` moved to `Oro\Bundle\MagentoBundle\Entity\Store`
* **MagentoBundle**: Class `Oro\Bundle\MagentoBundle\Entity\MagentoSoapTransport' was changed. Now it consists of fields and methods that are specific for SoapTransport.
* **MagentoBundle**: Class `Oro\Bundle\MagentoBundle\Entity\Repository\MagentoSoapTransportRepository` was renamed to `Oro\Bundle\MagentoBundle\Entity\Repository\MagentoTransportRepository`
* **MagentoBundle**: Class `Oro\Bundle\MagentoBundle\Form\EventListener\SoapConnectorsFormSubscriber` was renamed to `Oro\Bundle\MagentoBundle\Form\EventListener\ConnectorsFormSubscriber`
    * added protected method `getFormChannelType`
* **MagentoBundle**: Class `Oro\Bundle\MagentoBundle\Form\EventListener\SoapSettingsFormSubscriber` was renamed to `Oro\Bundle\MagentoBundle\Form\EventListener\SettingsFormSubscriber` and its service were renamed to `oro_magento.form.subscriber.transport_setting`
    * protected method `getModifierWebsitesList` was renamed to `modifyWebsitesList` and now it returns void.
* **MagentoBundle**: Class `Oro\Bundle\MagentoBundle\Form\Type\SoapTransportCheckButtonType` was renamed to `Oro\Bundle\MagentoBundle\Form\Type\TransportCheckButtonType`
* **MagentoBundle**: Method `getSores` in `Oro\Bundle\MagentoBundle\Provider\CartExpirationProcessor` was renamed to `getStores`
* **MagentoBundle**: Class `Oro\Bundle\MagentoBundle\Provider\AbstractMagentoConnector` was moved to `Oro\Bundle\MagentoBundle\Connector\AbstractMagentoConnector`
* **MagentoBundle**: Class `Oro\Bundle\MagentoBundle\Provider\CartConnector` was moved to `Oro\Bundle\MagentoBundle\Connector\CartConnector
* **MagentoBundle**: Class `Oro\Bundle\MagentoBundle\Provider\OrderConnector` was moved to `Oro\Bundle\MagentoBundle\Connector\OrderConnector`
* **MagentoBundle**: Class `Oro\Bundle\MagentoBundle\Provider\RegionConnector` was moved to `Oro\Bundle\MagentoBundle\Connector\RegionConnector`
* **MagentoBundle**: Class `Oro\Bundle\MagentoBundle\Provider\CustomerConnector` was moved to `Oro\Bundle\MagentoBundle\Connector\CustomerConnector`
* **MagentoBundle**: Class `Oro\Bundle\MagentoBundle\Provider\NewsletterSubscriberConnector` was moved to `Oro\Bundle\MagentoBundle\Connector\NewsletterSubscriberConnector`
* **MagentoBundle**: Class `Oro\Bundle\MagentoBundle\Provider\MagentoConnectorInterface` was moved to `Oro\Bundle\MagentoBundle\Connector\MagentoConnectorInterface`
* **MagentoBundle**: Class `Oro\Bundle\MagentoBundle\Provider\Customer\CustomerIconProvider`. Its service was renamed to `oro_magento.provider.customer.magento_customer_icon`
* **MagentoBundle**: Class `Oro\Bundle\MagentoBundle\Provider\Iterator\AbstractLoadeableSoapIterator` was renamed to `Oro\Bundle\MagentoBundle\Provider\Iterator\AbstractLoadeableIterator`
* **MagentoBundle**: Class `Oro\Bundle\MagentoBundle\Provider\Iterator\AbstractPageableSoapIterator` was renamed to `Oro\Bundle\MagentoBundle\Provider\Iterator\AbstractPageableIterator`
* **MagentoBundle**: Interface `Oro\Bundle\MagentoBundle\Provider\Iterator\NewsletterSubscriberBridgeIteratorInterface` was added
* **MagentoBundle**: Class `Oro\Bundle\MagentoBundle\Provider\Iterator\AbstractBridgeIterator` was moved to `Oro\Bundle\MagentoBundle\Provider\Iterator\Soap\AbstractBridgeIterator`
* **MagentoBundle**: Class `Oro\Bundle\MagentoBundle\Provider\Iterator\CartsBridgeIterator` was moved to `Oro\Bundle\MagentoBundle\Provider\Iterator\Soap\CartsBridgeIterator`
* **MagentoBundle**: Class `Oro\Bundle\MagentoBundle\Provider\Iterator\CustomerBridgeIterator` was moved to `Oro\Bundle\MagentoBundle\Provider\Iterator\Soap\CustomerBridgeIterator`
* **MagentoBundle**: Class `Oro\Bundle\MagentoBundle\Provider\Iterator\CustomerSoapIterator` was moved to `Oro\Bundle\MagentoBundle\Provider\Iterator\Soap\CustomerSoapIterator`
* **MagentoBundle**: Class `Oro\Bundle\MagentoBundle\Provider\Iterator\CustomerGroupBridgeIterator` was moved to `Oro\Bundle\MagentoBundle\Provider\Iterator\Soap\CustomerGroupBridgeIterator`
* **MagentoBundle**: Class `Oro\Bundle\MagentoBundle\Provider\Iterator\NewsletterSubscriberBridgeIterator` was moved to `Oro\Bundle\MagentoBundle\Provider\Iterator\Soap\NewsletterSubscriberBridgeIterator` and now implements `NewsletterSubscriberBridgeIteratorInterface`
* **MagentoBundle**: Class `Oro\Bundle\MagentoBundle\Provider\Iterator\OrderBridgeIterator` was moved to `Oro\Bundle\MagentoBundle\Provider\Iterator\Soap\OrderBridgeIterator`
* **MagentoBundle**: Class `Oro\Bundle\MagentoBundle\Provider\Iterator\OrderSoapIterator` was moved to `Oro\Bundle\MagentoBundle\Provider\Iterator\Soap\OrderSoapIterator`
* **MagentoBundle**:  Class `Oro\Bundle\MagentoBundle\Provider\Iterator\RegionSoapIterator` was moved to `Oro\Bundle\MagentoBundle\Provider\Iterator\Soap\RegionSoapIterator`
    * protected method `findEntitiesToProcess()` was moved to parent class
    * protected method `getEntityIds()` was moved to parent class
    * protected method `getEntity($id)` was moved to parent class
    * protected method `getIdFieldName()` was moved to parent class
    * protected method `current()` was moved to parent class
* **MagentoBundle**: Class `Oro\Bundle\MagentoBundle\Provider\Iterator\WebsiteSoapIterator` was moved to `Oro\Bundle\MagentoBundle\Provider\Iterator\Soap\WebsiteSoapIterator`
* **MagentoBundle**: Class `Oro\Bundle\MagentoBundle\Provider\Transport\SoapTransport` now implements `TransportCacheClearInterface`
    * Updated according to `Oro\Bundle\MagentoBundle\Provider\Transport\MagentoTransportInterface` changes.
    * public method `isCustomerHasUniqueEmail(Customer $customer)` was added
    * public method `getRequiredExtensionVersion()` was added
    * public method `cacheClear($resource = null)` was added
    * public method `getCreditMemos()` was added
    * public method `getCreditMemoInfo($incrementId)` was added
* **MagentoBundle**: Class `Oro\Bundle\MagentoBundle\Entity\Order` changed
    * field `originId` added
    * `Oro\Bundle\MagentoBundle\Entity\OriginTrait` used
* **MagentoBundle**: Class `Oro\Bundle\MagentoBundle\Autocomplete\IntegrationAwareSearchHandler`
    * method `setSecurityFacade` was replaced with `setAuthorizationChecker`
* **MagentoBundle**: Class `Oro\Bundle\MagentoBundle\Datagrid\NewsletterSubscriberPermissionProvider`
    * method `setSecurityFacade` was replaced with `setAuthorizationChecker`

### Removed
* **MagentoBundle**: Interface `Oro\Bundle\MagentoBundle\Provider\Transport\MagentoTransportInterface`
    * removed the `call` method because it conflicts with REST conception. From now on, MagentoTransportInterface will not allow to specify http methods and resource through parameters.
* **MagentoBundle**: Route `oro_magento_soap_check` was renamed to `oro_magento_integration_check`
* **MagentoBundle**: Translation with key `not_valid_parameters` was removed
* **MagentoBundle**: Process `magento_schedule_integration` was removed.

## 2.2.6 (2017-08-30)
## 2.2.5 (2017-08-22)
## 2.2.4 (2017-08-16)
## 2.2.3 (2017-07-27)
## 2.2.2 (2017-06-30)
## 2.2.1 (2017-06-08)
## 2.2.0 (2017-05-31)
[Show detailed list of changes](#file-incompatibilities-2-2-0.md)

* **SalesBundle**: Implementation of REST API for customer association was changed.
    * removed the following services:
        * `oro_sales.api.get_config.customer_association`
        * `oro_sales.api.customize_loaded_data.customer_association`
        * `oro_sales.api.get_metadata.customer_association`
        * `oro_sales.api.get_metadata.get_config`
        * `oro_sales.api.load_data.customer_association`
        * `oro_sales.api.opportunity.initialize_customer_type_guesser.customer_association`
        * `oro_sales.api.opportunity.initialize_customer_account_type_guesser`
        * `oro_sales.form.guesser.customer_guesser`
        * `oro_sales.form.guesser.customer_account_guesser`
        * `oro_sales.form.type.customer_api` (API form type alias `oro_sales_customer_api`)
        * `oro_sales.form.type.customer_account_api` (API form type alias `oro_sales_customer_account_api`)
    * the logic related to the customer association is implemented in `CustomerAssociationListener`<sup>[[?]](https://github.com/laboro/dev/blob/maintenance/2.2/package/crm/src/Oro/Bundle/SalesBundle/Api/Form/EventListener/CustomerAssociationListener.php "Oro\Bundle\SalesBundle\Api\Form\EventListener\CustomerAssociationListener")</sup>

## 2.1.6 (2017-06-30)
## 2.1.5 (2017-06-08)
## 2.1.4 (2017-05-30)
## 2.1.3 (2017-05-22)
## 2.1.2 (2017-05-11)
## 2.1.1 (2017-04-26)
## 2.1.0 (2017-03-30)
[Show detailed list of changes](#file-incompatibilities-2-1-0.md)

### Changed
* **AnalyticsBundle**: Class `Oro\Bundle\AnalyticsBundle\Builder\RFMBuilder`
    * changed the return type of `getEntityIdsByChannel` method from `\ArrayIterator|BufferedQueryResultIterator` to `\Iterator`
* **ChannelBundle**: Class `Oro\Bundle\ChannelBundle\Command\RecalculateLifetimeCommand`
    * changed the return type of `getCustomersIterator` method from `BufferedQueryResultIterator` to `BufferedQueryResultIteratorInterface`
* **ChannelBundle**: Class `Oro\Bundle\ChannelBundle\EventListener\AccountLifetimeSubscriber`
    * changed the return type of `getCustomersIterator` method from `BufferedQueryResultIterator` to `BufferedQueryResultIteratorInterface`
    * the visibility of property `$accounts` was changed from `protected` to `private`
    * the visibility of methods `createNoCustomerCondition`, `scheduleOpportunityAccount`, `scheduleCustomerAccounts`, `scheduleAccount` were changed from `protected` to `private`
* **ChannelBundle**: The following services were marked as `private`:
    * `oro_channel.twig.metadata_extension`
    * `oro_channel.twig.lifetime_value_extension`
* **ContactBundle**: The service `oro_contact.twig.extension.social_url` was renamed to `oro_contact.twig.extension` and marked as `private`
* **ContactBundle**: Class `Oro\Bundle\ContactBundle\Twig\SocialUrlExtension` was renamed to `Oro\Bundle\ContactBundle\Twig\ContactExtension`
* **SalesBundle**: Class `Oro\Bundle\SalesBundle\Datagrid\Extension\Customers\AccountExtension`:
    * added UnsupportedGridPrefixesTrait
* **SalesBundle**: opportunity` and `lead` apis changed:
    * `customerAssociation` relation replaced by `customer` and `account` relations
* **CRMBundle**: Updated service definition for `oro_crm.extension.transtation_packages_provider`:
    * changed class to `Oro\Bundle\FrontendBundle\Provider\TranslationPackagesProviderExtension`
    * changed publicity to `false

### Deprecated
* **SalesBundle**: Class `Oro\Bundle\SalesBundle\Entity\Repository\OpportunityRepository`:
    * Method `getNewOpportunitiesAmount` was marked as deprecated. Method `getOpportunitiesByPeriodQB` should be used instead
    * Method `getWonOpportunitiesToDateCount` was marked as deprecated. Method `getWonOpportunitiesCountByPeriodQB` should be used instead

### Removed
* **ChannelBundle**: Removed the following parameters from DIC:
    * `oro_channel.twig.metadata_extension.class`
    * `oro_channel.twig.lifetime_value_extension.class`
* **ContactBundle**: Removed the following parameters from DIC:
    * `oro_contact.twig.extension.social_url.class`

## 2.0.20 (2017-08-29)
## 2.0.19 (2017-08-23)
## 2.0.18 (2017-08-16)
## 2.0.17 (2017-07-27)
## 2.0.16 (2017-07-12)
## 2.0.15 (2017-06-30)
## 2.0.14 (2017-06-09)
## 2.0.13 (2017-06-08)
## 2.0.12 (2017-05-30)
## 2.0.11 (2017-05-23)
## 2.0.10 (2017-05-18)
## 2.0.9 (2017-05-12)
## 2.0.8 (2017-04-26)
## 2.0.7 (2017-04-19)
## 2.0.6 (2017-04-14)
## 2.0.5 (2017-04-14)
## 2.0.4 (2017-03-21)
## 2.0.3 (2017-03-21)
## 2.0.2 (2017-02-21)
## 2.0.1 (2017-02-06)
## 2.0.0 (2017-01-16)

This changelog references the relevant changes (new features, changes and bugs) done in 2.0 versions.
  * Changed minimum required php version to 5.6
  * Added support of PHP 7.1

## 1.10.0

This changelog references the relevant changes (new features, changes and bugs) done in 1.10.0 versions.
  * The application has been upgraded to Symfony 2.8 (Symfony 2.8.10 doesn't supported because of [Symfony issue](https://github.com/symfony/symfony/issues/19840))
  * Added support php 7
  * Changed minimum required php version to 5.5.9

## 1.9.0

This changelog references the relevant changes (new features, changes and bugs) done in 1.9.0 versions.
* 1.9.0 (2016-02-15)
 * Filter records by teams
 * Pipeline forecast widget and report
 * Contexts for all activities
 * Account activities
 * Unread email widget for the sidebar panel
 * Activities are available in the Merge Accounts dialog, allowing you to specify merge strategy for every type of activity
 * Filter emails by correspondent on the My Emails page
 * Segment Magento customers by coupons and discounts applied to their orders

## 1.8.0

This changelog references the relevant changes (new features, changes and bugs) done in 1.8.0 versions.
* 1.8.0 (2015-08-26)
 * Improved Email capabilities and features
 * Email automation
 * Contact history and last contact date is tracked for all records, allowing to segment them based on number of contacts, or date or direction of last contact
 * Tags may be used as filtering conditions in segments and grids
 * UX for Ecommerce Statistics widget has been improved

## 1.7.0

This changelog references the relevant changes (new features, changes and bugs) done in 1.7.0 versions.
* 1.7.0 (2015-04-28)
 * Tracking of email conversations and threads
 * Email signatures
 * Email attachments
 * Email contexts
 * Immediate availability of Magento data after initial synchronization
 * Automatic accounts discovery on Magento customers sync
 * Create and Edit Magento customers from OroCRM
 * Import of Magento newsletter subscribers
 * Connection between web events and CRM data
 * Connect guest/anonymous web events to customer after authentication
 * Abandoned shopping cart campaigns
 * New widgets for eCommerce dashboard
 * Dropped support of Magento 1.6 due to API limitations.

## 1.6.0

This changelog references the relevant changes (new features, changes and bugs) done in 1.6.0 versions.
* 1.6.0 (2015-01-19)
 * Availability of email campaign results for filtering in reports & segments.
Now email campaign results, such as opens, clicks, bounces, etc., are available for filter queries in reporting and customer segmentation. This also includes campaign statistics received via MailChimp integration

## 1.5.0

This changelog references the relevant changes (new features, changes and bugs) done in 1.5.0 versions.
* 1.5.0 (2014-12-18)
 * RFM analytic for Magento channels.
RFM is a popular set of metrics used to analyze customer value and to determine the best customers, especially for retail and e-commerce. The 1.5.0 release of OroCRM adds the ability to configure RFM metrics for Magento channels.
The RFM score consists of three metrics:
 - Recency, that evaluates the number of days that passed since the last purchase. The more recent is the purchase, the better.
 - Frequency, that evaluates the number of orders placed by the customer in the last 365 days. The more frequently the customer buys, the better.
 - Monetary value, that evaluates the total amount of orders placed by the customer in the last 365 days. The more money customer spends, the better.
To construct these metrics, the entire range of values is divided into a small number of categories, or "buckets." The number of buckets usually lies in range of 3 to 10, and scores for R, F, and M range accordingly—from 1 (the best score) to the maximum number of buckets (the worst score). You can change the number of buckets and move their boundaries in order to adjust the scores to characteristic values of your business.
After the metric is applied, every customer gets a three-number set of RFM scores. R1 F1 M1 identifies the best customers, and the higher the scores are, the worse these customers perform in a particular field.
RFM scores are displayed on the Magento customer view page and on the customer section of the Account view. You may also re-use these scores in reporting and segmentation.

## 1.4.0

This changelog references the relevant changes (new features, changes and bugs) done in 1.4.0 versions.
* 1.4.0 (2014-10-15)
 * The re-introduction of Channels.
We started the implementation of a new vision for the Channels in 1.3 version and now we bring Channels back, although under a new definition.
The general idea behind channels may be explained as follows: a channel in OroCRM represents an outside source customer and sales data, where "customer" and "sales" must be understood in the broadest sense possible. Depending on the nature of the outside source, the channel may or may not require a data integration.
This new definition leads to multiple noticeable changes across the system.
 * Accounts.
Account entity now performs as the "umbrella" entity for all customer identities across multiple channels, displaying all their data in a single view.
 * Integration management.
Albeit the Integrations grid still displays all integrations that exist in the system, you now may create only "non-customer" standalone integrations, such as Zendesk integration. The "customer" integrations, such as Magento integration, may be created only in scope of a channel and cannot exist without it.
 * Channel management UI.
The UI for channel creation now allows the user to specify channel type. By default there are three channel types: Magento, B2B, and Custom; more channel types may be created by developers.
Each channel type characterizes the following:
Whether a channel requires an integration. If the answer is yes (cf. Magento), the integration should be configured along the creation of the channel.
Which entity will serve as the Customer Identity. This entity cannot be changed by the user.
Which entities will be enabled in the system along with the channel.
A specific set of entities comes by default (e.g. Sales Process, Lead, and Opportunity for B2B channel), but the user may remove or add entities if necessary.
 * B2B functionality.
B2B functionality, such as Leads or Opportunities will no longer be available by default—in order to work with them the user should create at least one B2B channel first. As a result it is now possible to configure your instance of OroCRM to be fully B2C-oriented and work only with entities that make sense in eCommerce context—with no mandatory Leads and Opportunities at all.
In order to comply with the new concept of Customer Identity, the new entity named B2B Customer was added to the system. It replaces Account in most cases of our default Sales Process workflows.
 * Lifetime sales value.
This feature provides the means to record historical sales for every channel type. The exact definition of what constitutes sales is subject to channel type: for Magento channels lifetime sales are counted as order subtotal (excluding cancelled orders), and for B2B channels it is counted as total value of won opportunities. The common metric allows you to quickly compare sales across channels in the account view, where both per-channel and account total values are displayed.
 * Marketing lists.
Marketing lists serve as the basis for marketing activities, such as email campaigns (see below). They represent a target auditory of the activity—that is, people, who will be contacted when the activity takes place. Marketing lists have little value by themselves; they exist in scope of some marketing campaign and its activities.
Essentially, marketing list is a segment of entities that contain some contact information, such as email or phone number or physical address. Lists are build based on some rules using Oro filtering tool. Similarly to segments, marketing lists can be static or dynamic; the rules are the same. The user can build marketing lists of contacts, Magento customers, leads, etc.
In addition to filtering rules, the user can manually tweak contents of the marketing list by removing items ("subscribers") from it. Removed subscribers will no longer appear in the list even if they fit the conditions. It is possible to move them back in the list, too.
Every subscriber can also unsubscribe from the list. In this case, he will remain in the list, but will no longer receive email campaigns that are sent to this list. Note that subscription status is managed on per-list basis; the same contact might be subscribed to one list and unsubscribed from another.
 * Email campaigns.
Email campaign is a first example of marketing activity implemented in OroCRM. The big picture is following: Every marketing campaign might contain multiple marketing activities, e.g. an email newsletter, a context ad campaign, a targeted phone advertisement. All these activities serve the common goal of the "big" marketing campaign.
In its current implementation, email campaign is a one-time dispatch of an email to a list of subscribers. Hence, the campaign consists of three basic parts:
Recipients—represented by a Marketing list.
Email itself—the user may choose a template, or create a campaign email from scratch.
Sending rules—for now, only one-time dispatch is available.
Email campaign might be tied to a marketing campaign, but it might exist on its own as well.
 * Ecommerce dashboard
In addition to default dashboard we have added a special Ecommerce-targeted board with three widgets:
<ul><li>Average order amount</li>
   <li>New web customers</li>
   <li>Average customer lifetime sales</li></ul>
Every widget displays historical trend for the particular value over the past 12 months. You can also add them to any other dashboard using the Add Widget button.

## 1.4.0-RC1

This changelog references the relevant changes (new features, changes and bugs) done in 1.4.0-RC1 versions.
* 1.4.0-RC1 (2014-09-30)
 * The re-introduction of Channels.
We started the implementation of a new vision for the Channels in 1.3 version and now we bring Channels back, although under a new definition.
The general idea behind channels may be explained as follows: a channel in OroCRM represents an outside source customer and sales data, where "customer" and "sales" must be understood in the broadest sense possible. Depending on the nature of the outside source, the channel may or may not require a data integration.
This new definition leads to multiple noticeable changes across the system.
 * Accounts.
Account entity now performs as the "umbrella" entity for all customer identities across multiple channels, displaying all their data in a single view.
 * Integration management.
Albeit the Integrations grid still displays all integrations that exist in the system, you now may create only "non-customer" standalone integrations, such as Zendesk integration. The "customer" integrations, such as Magento integration, may be created only in scope of a channel and cannot exist without it.
 * Channel management UI.
The UI for channel creation now allows the user to specify channel type. By default there are three channel types: Magento, B2B, and Custom; more channel types may be created by developers.
Each channel type characterizes the following:
Whether a channel requires an integration. If the answer is yes (cf. Magento), the integration should be configured along the creation of the channel.
Which entity will serve as the Customer Identity. This entity cannot be changed by the user.
Which entities will be enabled in the system along with the channel.
A specific set of entities comes by default (e.g. Sales Process, Lead, and Opportunity for B2B channel), but the user may remove or add entities if necessary.
 * B2B functionality.
B2B functionality, such as Leads or Opportunities will no longer be available by default—in order to work with them the user should create at least one B2B channel first. As a result it is now possible to configure your instance of OroCRM to be fully B2C-oriented and work only with entities that make sense in eCommerce context—with no mandatory Leads and Opportunities at all.
In order to comply with the new concept of Customer Identity, the new entity named B2B Customer was added to the system. It replaces Account in most cases of our default Sales Process workflows.
 * Lifetime sales value.
This feature provides the means to record historical sales for every channel type. The exact definition of what constitutes sales is subject to channel type: for Magento channels lifetime sales are counted as order subtotal (excluding cancelled orders), and for B2B channels it is counted as total value of won opportunities. The common metric allows you to quickly compare sales across channels in the account view, where both per-channel and account total values are displayed.
 * Marketing lists.
Marketing lists serve as the basis for marketing activities, such as email campaigns (see below). They represent a target auditory of the activity—that is, people, who will be contacted when the activity takes place. Marketing lists have little value by themselves; they exist in scope of some marketing campaign and its activities.
Essentially, marketing list is a segment of entities that contain some contact information, such as email or phone number or physical address. Lists are build based on some rules using Oro filtering tool. Similarly to segments, marketing lists can be static or dynamic; the rules are the same. The user can build marketing lists of contacts, Magento customers, leads, etc.
In addition to filtering rules, the user can manually tweak contents of the marketing list by removing items ("subscribers") from it. Removed subscribers will no longer appear in the list even if they fit the conditions. It is possible to move them back in the list, too.
Every subscriber can also unsubscribe from the list. In this case, he will remain in the list, but will no longer receive email campaigns that are sent to this list. Note that subscription status is managed on per-list basis; the same contact might be subscribed to one list and unsubscribed from another.
 * Email campaigns.
Email campaign is a first example of marketing activity implemented in OroCRM. The big picture is following: Every marketing campaign might contain multiple marketing activities, e.g. an email newsletter, a context ad campaign, a targeted phone advertisement. All these activities serve the common goal of the "big" marketing campaign.
In its current implementation, email campaign is a one-time dispatch of an email to a list of subscribers. Hence, the campaign consists of three basic parts:
Recipients—represented by a Marketing list.
Email itself—the user may choose a template, or create a campaign email from scratch.
Sending rules—for now, only one-time dispatch is available.
Email campaign might be tied to a marketing campaign, but it might exist on its own as well.
 * Ecommerce dashboard
In addition to default dashboard we have added a special Ecommerce-targeted board with three widgets:
<ul><li>Average order amount</li>
   <li>New web customers</li>
   <li>Average customer lifetime sales</li></ul>
Every widget displays historical trend for the particular value over the past 12 months. You can also add them to any other dashboard using the Add Widget button.

## 1.3.1

This changelog references the relevant changes (new features, changes and bugs) done in 1.3.1 versions.

* 1.3.1 (2014-08-14)
 * Magento Synchronization stabilization improvements
 * Fixed issue: Incorrect row count on grids.
 * Fixed issue: Reports and Segments crash when "Is empty" filter is added.
 * Fixed issue: Recent Emails dashboard widget is broken.
 * Fixed issue: Accounts cannot be linked to Contacts from Edit Contact page.

## 1.3.0

This changelog references the relevant changes (new features, changes and bugs) done in 1.3.0 versions.

* 1.3.0 (2014-07-23)
 * Website event tracking
 * Marketing campaigns
 * Campaign code tracking
 * Cases
 * Processes within Magento integration
 * Activities (Notes, Emails, Attachments)
 * Data import in CSV format
 * Zendesk integration
 * Other changes and improvements

## 1.2.0

This changelog references the relevant changes (new features, changes and bugs) done in 1.2.0 versions.

* 1.2.0 (2014-05-28)
 * Two-side customer data synchronization with Magento
 * Improvements to Customer view
 * Improvements to Magento data view
 * Fixed issue Broken widgets in merged Account view
 * Fixed Community requests

## 1.2.0-rc1

This changelog references the relevant changes (new features, changes and bugs) done in 1.2.0 RC1 versions.

* 1.2.0 RC1 (2014-05-12)
 * Improvements to Customer view
 * Improvements to display of Magento data
 * Fixed issue Broken widgets in merged Account view

## 1.0.0

This changelog references the relevant changes (new features, changes and bugs) done in 1.0.0 versions.

* 1.0.0 (2014-04-01)
 * Tasks
 * Improved UI for launch of the Sales Process workflow

## 1.0.0-rc2

This changelog references the relevant changes (new features, changes and bugs) done in 1.0.0-rc2 versions.

* 1.0.0-rc2 (2014-02-25)
 * Refactored Flexible Workflows
 * Embedded forms
 * Account merging

## 1.0.0-rc1

This changelog references the relevant changes (new features, changes and bugs) done in 1.0.0-rc1 versions.

* 1.0.0-rc1 (2014-01-30)
 * Improved Reports
 * Improved Workflow
 * Improved Dashboard
 * Magento import performance improvements
 * Other improvements in channnels, contacts

## 1.0.0-beta6

This changelog references the relevant changes (new features, changes and bugs) done in 1.0.0-beta6 versions.

* 1.0.0-beta6 (2013-12-30)
 * Magento data import: Customers, Shopping carts and Orders
 * B2C Sales Flow
 * Call view window
 * Basic dashboards

## 1.0.0-beta5

This changelog references the relevant changes (new features, changes and bugs) done in 1.0.0-beta5 versions.

* 1.0.0-beta5 (2013-12-05)
 * Reports creation wizard (Table reports)
 * B2B Sales Flow adjustments
 * Call entity
 * Add weather layer in the map on contact view page

## 1.0.0-beta4

This changelog references the relevant changes (new features, changes and bugs) done in 1.0.0-beta4 versions.

* 1.0.0-beta4 (2013-11-21)
 * Workflow transitions
 * Make all entities as Extended
 * End support for Internet Explorer 9

## 1.0.0-beta3

This changelog references the relevant changes (new features, changes and bugs) done in 1.0.0-beta3 versions.

* 1.0.0-beta3 (2013-11-11)
  * Oro Platform Beta 3 dependency changes

## 1.0.0-beta2

This changelog references the relevant changes (new features, changes and bugs) done in 1.0.0-beta2 versions.

* 1.0.0-beta2 (2013-10-28)
  * Oro Platform Beta 2 dependency changes

## 1.0.0-beta1

This changelog references the relevant changes (new features, changes and bugs) done in 1.0.0-beta1 versions.

* 1.0.0-beta1 (2013-09-30)
  * CRM Entities reports
  * Contacts Import/Export
  * Account association with contacts
  * Custom entities and fields in usage

## 1.0.0-alpha6

* 1.0.0-alpha6 (2013-09-12)
  * Leads and Opportunities
  * Flexible Workflow Engine (FWE)

## 1.0.0-alpha5

* 1.0.0-alpha5 (2013-08-29)
 * Contacts Improvements
   * added ability to manage addresses from contact view page with Google Maps API support
   * added support of multiple Emails and Phones for Contact

## 1.0.0-alpha4

* 1.0.0-alpha4 (2013-07-31)
 * Address Types Management. Added ability to set different type for addresses in Contact address book

## 1.0.0-alpha3

This changelog references the relevant changes (new features, changes and bugs) done in 1.0.0-alpha3 versions.

* 1.0.0-alpha3 (2013-06-27)
 * Placeholders
 * Developer toolbar works with AJAX navigation requests
 * Configuring hidden columns in a Grid
 * Auto-complete form type
 * Added Address Book
 * Many-to-many relation between Contacts and Accounts
 * Added ability to sort Contacts and Accounts by Phone and Email in a Grid
 * Localized countries and regions
 * Enhanced data change log with ability to save changes for collections
 * Removed dependency on lib ICU

