The upgrade instructions are available at [Oro documentation website](https://doc.oroinc.com/master/backend/setup/upgrade-to-new-version/).

The current file describes significant changes in the code that may affect the upgrade of your customizations.

## Changes in the СRM package versions

- [5.1.0](#510-2023-03-31)
- [5.0.0](#500-2022-01-26)
- [4.2.0](#420-2020-01-29)
- [4.1.0](#410-2020-01-31)
- [4.0.0](#400-2019-07-31)
- [3.1.4](#314)
- [3.1.0](#310-2019-01-30)
- [3.0.0](#300-2018-07-27)
- [2.6.0](#260-2018-01-31)
- [2.5.0](#250-2017-11-30)
- [2.4.0](#240-2017-09-29)
- [2.3.0](#230-2017-07-28)
- [2.2.0](#220-2017-05-31)
- [2.1.0](#210-2017-03-30)
- [2.0.0](#200-2017-01-16)
- [1.10.0](#1100)
- [1.9.0](#190-2016-02-15)
- [1.8.0](#180-2015-08-26)
- [1.7.0](#170-2015-04-28)
- [1.6.0](#160-2015-01-19)
- [1.5.0](#150-2014-12-18)
- [1.4.0](#140-2014-10-15)
- [1.3.1](#131-2014-08-14)
- [1.3.0](#130-2014-07-23)
- [1.2.0](#120-2014-05-28)
- [1.0.0](#100-2014-04-01)



## 5.1.0 (2023-03-31)

[Show detailed list of changes](incompatibilities-5-1.md)

## 5.0.0 (2022-01-26)

[Show detailed list of changes](incompatibilities-5-0.md)

### Removed

#### ContactBundle
* `PrepareResultItemListener` has been removed as now search engine generates and stores entity name in the index in a separate field
* Removed `\Oro\Bundle\ContactBundle\ImportExport\Strategy\ContactAddOrReplaceStrategy::importExistingEntity`, functionality covered by `\Oro\Bundle\ImportExportBundle\Strategy\Import\ConfigurableAddOrReplaceStrategy`
* Removed `\Oro\Bundle\ContactBundle\ImportExport\Strategy\ContactAddOrReplaceStrategy::fixDuplicateEntities`, functionality covered by `\Oro\Bundle\ImportExportBundle\Strategy\Import\ConfigurableAddOrReplaceStrategy`

#### CustomerBundle
* Removed `\Oro\Bundle\CustomerBundle\ImportExport\Strategy\CustomerAddOrReplaceStrategy::processValidationErrors`, functionality covered by `\Oro\Bundle\ImportExportBundle\Strategy\Import\ConfigurableAddOrReplaceStrategy`

## 4.2.0 (2020-01-29)
[Show detailed list of changes](incompatibilities-4-2.md)

The code of OroCRM connector to Magento 1 has been moved to a separate package.


## 4.1.0 (2020-01-31)

[Show detailed list of changes](incompatibilities-4-1.md)

### Removed
* The `*.class` parameters for all entities were removed from the dependency injection container.
The entity class names should be used directly, e.g.,`'Oro\Bundle\EmailBundle\Entity\Email'`
instead of `'%oro_email.email.entity.class%'` (in service definitions, datagrid config files, placeholders, etc.), and
`\Oro\Bundle\EmailBundle\Entity\Email::class` instead of `$container->getParameter('oro_email.email.entity.class')`
(in PHP code).

#### ActivityContactBundle
* The `getSupportedClass()` method was removed from `Oro\Bundle\ActivityContactBundle\Direction\DirectionProviderInterface`.
  Use the `class` attribute of the `oro_activity_direction.provider` DIC tag instead.

#### All Bundles
* All `*.class` parameters for service definitions were removed from the dependency injection container.

#### MagentoBundle
* The following deprecated API resources were removed:
    - `GET /api/rest/{version}/customers.{_format}`, use `GET /api/rest/{version}/magentocustomers.{_format}` instead
    - `POST /api/rest/{version}/customers.{_format}`, use `POST /api/rest/{version}/magentocustomers.{_format}` instead
    - `GET /api/rest/{version}/customers/{id}`, use `GET /api/rest/{version}/magentocustomers/{id}.{_format}` instead
    - `PUT /api/rest/{version}/customers/{id}.{_format}`, use `PUT /api/rest/{version}/magentocustomers/{id}.{_format}` instead
    - `DELETE /api/rest/{version}/customers/{id}.{_format}`, use `DELETE /api/rest/{version}/magentocustomers/{id}.{_format}` instead

#### SalesBundle
* The deprecated API resource `GET /api/rest/{version}/leads/{leadId}/address.{_format}` was removed,
  use `GET /api/rest/{version}/leads/{leadId}/addresses.{_format}` instead.

## 4.0.0 (2019-07-31)
[Show detailed list of changes](incompatibilities-4-0.md)

### Changed
#### ChannelBundle
* Method `getSettings` has been removed from `Oro\Bundle\ChannelBundle\Provider\SettingsProvider`.
  Use `getChannelTypes` and `getEntities` methods instead.
* Method `Oro\Bundle\ChannelBundle\Provider\SettingsProvider::getDependentEntityData` has been renamed
  to `getDependentEntities`.
* Method `Oro\Bundle\ChannelBundle\Provider\SettingsProvider::isChannelSystem` has been renamed to `isSystemChannel`.

#### ContactUsBundle
* In `Oro\Bundle\ContactUsBundle\Controller\ContactReasonController::deleteAction` 
 (`oro_contactus_reason_delete` route)
 action the request method was changed to DELETE. 
* In `Oro\Bundle\ContactUsBundle\Controller\ContactRequestController::deleteAction` 
 (`oro_contactus_request_create` route)
 action the request method was changed to DELETE. 
 
#### MagentoBundle
* In `Oro\Bundle\MagentoBundle\Controller\CustomerController::registerAction` 
 (`oro_magento_customer_register` route)
 action the request method was changed to POST.
* In `Oro\Bundle\MagentoBundle\Controller\IntegrationConfigController::checkAction` 
 (`oro_magento_integration_check` route)
 action the request method was changed to POST. 
* In `Oro\Bundle\MagentoBundle\Controller\NewsletterSubscriberController::subscribeAction` 
 (`oro_magento_newsletter_subscriber_subscribe` route)
 action the request method was changed to POST. 
* In `Oro\Bundle\MagentoBundle\Controller\NewsletterSubscriberController::unsubscribeAction` 
 (`oro_magento_newsletter_subscriber_unsubscribe` route)
 action the request method was changed to POST.
* In `Oro\Bundle\MagentoBundle\Controller\NewsletterSubscriberController::subscribeByCustomerAction` 
 (`oro_magento_newsletter_subscriber_subscribe_customer` route)
 action the request method was changed to POST. 
* In `Oro\Bundle\MagentoBundle\Controller\NewsletterSubscriberController::unsubscribeByCustomerAction` 
 (`oro_magento_newsletter_subscriber_unsubscribe_customer` route)
 action the request method was changed to POST. 
* In `Oro\Bundle\MagentoBundle\Controller\OrderPlaceController::syncAction` 
 (`oro_magento_orderplace_new_cart_order_sync` route)
 action the request method was changed to POST. 
* In `Oro\Bundle\MagentoBundle\Controller\OrderPlaceController::customerSyncAction` 
 (`oro_magento_orderplace_new_customer_order_sync` route)
 action the request method was changed to POST. 
 
### Removed
* Service `oro_channel.provider.exclusion_provider` and related logic were removed. There is
no exclusion for "channel type" entities on UI.




## 3.1.4 

### Removed
#### CRMBundle
* Service `oro_crm.namespace_migration_provider` and the logic that used it were removed.

## 3.1.0 (2019-01-30)
[Show detailed list of changes](incompatibilities-3-1.md)

* Package `oro/crm-mail-chimp` removed from composer.json, run `composer require 'oro/mailchimp:3.1.*'` before upgrade to keep functionality working
* Package `oro/crm-abandoned-cart` removed from composer.json, run `composer require 'oro/magento-abandoned-cart:3.1.*'` before upgrade to keep functionality working

### Changed

#### ContactBundle
* Changes in `/api/contactaddresses` REST API resource:
    - the attribute `created` was renamed to `createdAt`
    - the attribute `updated` was renamed to `updatedAt`
#### MagentoBundle
* Changes in `/api/magentoaddresses` REST API resource:
    - the attribute `created` was renamed to `createdAt`
    - the attribute `updated` was renamed to `updatedAt`
* Changes in `/api/magentocartaddresses` REST API resource:
    - the attribute `created` was renamed to `createdAt`
    - the attribute `updated` was renamed to `updatedAt`
#### SalesBundle
* Changes in `/api/leadaddresses` REST API resource:
    - the attribute `created` was renamed to `createdAt`
    - the attribute `updated` was renamed to `updatedAt`


## 3.0.0 (2018-07-27)

[Show detailed list of changes](incompatibilities-3-0.md)


## 2.6.0 (2018-01-31)
[Show detailed list of changes](incompatibilities-2-6.md)


### Added
#### MagentoBundle
* Traits `CreatedAtTrait` and `UpdatedAtTrait` were added. Use them instead of adding new `updatedAt` and `createdAt` fields to the entity.
* Interface `CreatedAtAwareInterface` and `UpdatedAtAwareInterface` were added. Use them with the `CreatedAtTrait` and `UpdatedAtTrait` traits.
* The new relation `orderNotes` was added to the `Order` entity. It contains a collection of notes attached to the current `Order`.
* The new entity `OrderNote` was added. It contains a single record of an `Order Note`.
* Class `IsDisplayOrderNotesSubscriber` was added. It blocks the `isDisplayOrderNotes` field when the current channel does not support the order notes functionality.
* Class `IsDisplayOrderNotesFormType` and its service `oro_magento_is_display_order_notes_type` were added to define all options required by the `isDisplayOrderNotes` field in one place.
* Class `OrderNotesDataConverter` and its service `oro_magento.importexport.data_converter.order_notes` were added to specify the converting logic for order notes from raw data to the data that is ready for deserialization.
* Interface `MagentoTransportInterface` was changed:
    * Added method `isSupportedOrderNoteExtensionVersion`. It uses to check that retrieved extension version from magento is supported order note functionality.
    * Added method `getOrderNoteRequiredExtensionVersion`. It is used to retrieve the required extension version from Magento that supports the order note functionality.
* Classes `RestTransport` and `SoapTransport` were changed. They introduced implementation of the new methods that were added to `MagentoTransportInterface`.
* Four datagrids were added to show order notes for customer, account, order.
[Documentation](./src/Oro/Bundle/MagentoBundle/Resources/doc/reference/order_notes_datagrid.md)
* Class `OrderNotesExtension` and its service `oro_magento.twig.order_notes_extension`. Use them to check if the notes tab or grid are allowed to be shown.
* Class `Context` was added. Use it to deliver data from `ChainProcessor` to its sub-processors.
* Class `ChainProcessor` and its service `oro_magento.importexport.processor.order_notes.chain_processor` were added. Use them to prepare `Order Note` collection for import.
* Interface `ProcessorInterface` was added. It is a contract for the sub-processors of `ChainProcessor`.
* Class `ValidationNoteProcessor` and its service `oro_magento.importexport.processor.order_notes.validation_note_processor` were added. It's a sub-processor of `ChainProcessor` that was added to filter invalid `Order Notes`.
* Class `NoteFieldsProcessor` and its service `oro_magento.importexport.processor.order_notes.note_fields_processor` were added. It's a sub-processor of `ChainProcessor` that was added to map selected fields from the `Order` to the `Order Note` entity and apply specific transformation functions to data that is kept in the `Order Note` fields.

#### ContactUsBundle
* Field `emailAddress` now have `"contact_information"="email"` entity property

### Changed
#### SalesBundle
* If you use REST API to manage the `Opportunity` entity, keep in mind that the `probability` field is limited by the range of values from 0 to 1. Validation was added. In PATCH and POST requests it is not transformed as before (it was divided by 100). So, if you add an opportunity via API with the 0.5 probability, you will receive the same value in GET request.

#### MagentoBundle
* Entity `MagentoTransport` was changed:
    * The new `isDisplayOrderNotes` field was added. It tells whether order notes are to be displayed on the `Magento Customer` and `Account` view pages.
    * Method `isSupportedOrderNoteExtensionVersion`. Use it to find out if channel configuration supports the synchronization of `Order Notes`.

### Removed
#### ContactBundle
* The parameter `oro_contact.subscriber.contact_listener.class` was removed form the service container

#### MagentoBundle
* The parameter `oro_magento.event_listener.customer_currency.class` was removed form the service container
* The parameter `oro_magento.event_listener.order.class` was removed form the service container
* The parameter `oro_magento.integration_entity.remove_listener.class` was removed form the service container






## 2.5.0 (2017-11-30)
[Show detailed list of changes](#incompatibilities-2-5.md)

### Added
#### ACL
* In case when CRM is installed together with Customer Portal, the `Account Manager` role has full permissions for Account and Contact entities. The permissions for the `Account Manager` is not changed if CRM is added to already installed Customer Portal.
#### MagentoBundle
* Two new datagrids were added to show purchased products from magento orders for customer and account.
[Documentation](./src/Oro/Bundle/MagentoBundle/Resources/doc/reference/purchased_products_datagrid.md)
* The new `sharedGuestEmailList` field was added to the `MagentoTransport` entity. During guest order synchronization, separate `MagentoCustomer` entities will be created for orders that have emails on the `sharedGuestEmailList` (`email`, `firstName` and `lastName` fields are used for identification).
* Class `EmailListToStringTransformer` was added. Use it to transform text with different delimiters between entries into the list of emails.
* Class `EmailAddressListValidator` was added. Use it to validate array of emails.
* Class `AbstractArrayToStringTransformer` was added. Use it to create your own transformers based on array to string transformation like `EmailListToStringTransformer`.
* Class `GuestCustomerStrategyHelper` and its service `oro_magento.importexport.guest_customer_strategy_helper` were added.
Use it to check if guest customer emails are on the `sharedGuestEmailList`, and retrieve identification data to search for existing guest customers.
* Class `SharedEmailListSubscriber` is added to the `sharedGuestEmailList` block field when OroBridge extension is not installed on the Magento side.
* Class `SharedGuestEmailListType` and its service `oro_magento_shared_guest_email_list_type` were added to define all options required by the `sharedGuestEmailList` field in one place.
#### ChannelBundle
* Class `RefreshChannelCacheListener` was added. Use it to refresh cache after channel create or delete.

### Changed
#### MagentoBundle
* Methods `transformArrayToString` and `transformStringToArray` were moved from `ArrayToStringTransformer` to `AbstractArrayToStringTransformer` and changed their visibility to `protected`
* Property `$delimiter` was moved from `ArrayToStringTransformer` to `AbstractArrayToStringTransformer` and changed its visibility to `protected`

### Removed
#### ChannelBundle
* Remove listener from `StateProvider` on next events:
    - `oro_channel.channel.save_succeed`
    - `oro_channel.channel.delete_succeed`

## 2.4.0 (2017-09-29)
[Show detailed list of changes](#incompatibilities-2-4.md)

### Changed
#### MagentoBundle
* The `SoapTransport` (Magento 1 default transport) and `RestTransport` (Magento 2)  classes changed format of the data 
returned by `getWebsites` method. The old response was the following:
```
[
    'id' => 'id', // Magento original webdsite id
    'code' => 'code',
    'name' => 'name',
    'default_group_id' => 'default group id'
]
```
The new response is the following:
```
[
    'website_id' => 'id', // Magento original webdsite id
    'code' => 'code',
    'name' => 'name',
    'default_group_id' => 'default group id'
]
```
### Removed
#### MagentoBundle
* The `WebsiteDataConverter`<sup>[[?]](https://github.com/oroinc/crm/tree/2.4.0/src/Oro/Bundle/MagentoBundle/ImportExport/Converter/Rest/WebsiteDataConverter.php "Oro\Bundle\MagentoBundle\ImportExport\Converter\Rest\WebsiteDataConverter")</sup>class was removed. The `WebsiteDataConverter`<sup>[[?]](https://github.com/oroinc/crm/tree/2.4.0/src/Oro/Bundle/MagentoBundle/ImportExport/Converter/WebsiteDataConverter.php "Oro\Bundle\MagentoBundle\ImportExport\Converter\WebsiteDataConverter")</sup> class should be used instead. In addition, the `@oro_magento.importexport.data_converter.rest.website`service was replaced with `@oro_magento.importexport.data_converter.website`.
* Class `AddressImportHelper`<sup>[[?]](https://github.com/oroinc/crm/blob/2.3.0/src/Oro/Bundle/MagentoBundle/ImportExport/Strategy/StrategyHelper/AddressImportHelper.php "Oro\Bundle\MagentoBundle\ImportExport\Strategy\StrategyHelper\AddressImportHelper")</sup>:
    * removed method `updateRegionByMagentoRegionIdOrUnsetNonSystemRegionOnly` use `updateRegionByMagentoRegionId` instead
    
    
## 2.3.0 (2017-07-28)
[Show detailed list of changes](#incompatibilities-2-3.md)

### Added
#### MagentoBundle
* Class `Magento2ChannelType`<sup>[[?]](https://github.com/oroinc/crm/tree/2.3.0/src/Oro/Bundle/MagentoBundle/Provider/Magento2ChannelType.php "Oro\Bundle\MagentoBundle\Provider\Magento2ChannelType")</sup> was added to support Magento2 as a new integration
* Class `IntegrationConfigController.php`<sup>[[?]](https://github.com/oroinc/crm/tree/2.3.0/src/Oro/Bundle/MagentoBundle/Controller/IntegrationConfigController.php.php "Oro\Bundle\MagentoBundle\Controller\IntegrationConfigController.php")</sup> was added. It is a universal entry point for both Magento and Magento2 integration check requests
* Class `MagentoTransport`<sup>[[?]](https://github.com/oroinc/crm/tree/2.3.0/src/Oro/Bundle/MagentoBundle/Entity/MagentoTransport.php "Oro\Bundle\MagentoBundle\Entity\MagentoTransport")</sup> was added. It's a parent for `MagentoSoapTransport` and `MagentoRestTransport` and it has all their similar properties and methods
* Class `TransportHandler`<sup>[[?]](https://github.com/oroinc/crm/tree/2.3.0/src/Oro/Bundle/MagentoBundle/Handler/TransportHandler.php "Oro\Bundle\MagentoBundle\Handler\TransportHandler")</sup> and its service `oro_magento.handler.transport` were added. It is a layer between transport and controller.
    * Method `getMagentoTransport` was added. Its main responsibility is to initialize and return MagentoTransport from check request.
    * Method `getCheckResponse`: returns array with data for response.
* Class `ProviderConnectorChoicesProvider`<sup>[[?]](https://github.com/oroinc/crm/tree/2.3.0/src/Oro/Bundle/MagentoBundle/ProviderConnectorChoicesProvider.php "Oro\Bundle\MagentoBundle\ProviderConnectorChoicesProvider")</sup> and its service `oro_magento.provider.connector_choices` were added. It has method:
    * `getAllowedConnectorsChoices` it returns a list of connectors available for some integration.
* Class `RestPingProvider`<sup>[[?]](https://github.com/oroinc/crm/tree/2.3.0/src/Oro/Bundle/MagentoBundle/Provider/RestPingProvider.php "Oro\Bundle\MagentoBundle\Provider\RestPingProvider")</sup> and its service `oro_magento.provider.rest_ping_provider` were added. Use it to send ping request to Magento and store response data.
* Class `RestRokenProvider`<sup>[[?]](https://github.com/oroinc/crm/tree/2.3.0/src/Oro/Bundle/MagentoBundle/Provider/RestRokenProvider.php "Oro\Bundle\MagentoBundle\Provider\RestRokenProvider")</sup> and its service `oro_magento.provider.rest_token_provider` were added. Use it to get a token, generate a new token and store it.
* Class `RestTransportAdapter`<sup>[[?]](https://github.com/oroinc/crm/tree/2.3.0/src/Oro/Bundle/MagentoBundle/Provider/Transport/RestTransportAdapter.php "Oro\Bundle\MagentoBundle\Provider\Transport\RestTransportAdapter")</sup> was added. It converts MagentoRestTransport entity to interface suitable for REST client factory.
* Class `RestTransport`<sup>[[?]](https://github.com/oroinc/crm/tree/2.3.0/src/Oro/Bundle/MagentoBundle/Provider/Transport/RestTransport.php "Oro\Bundle\MagentoBundle\Provider\Transport\RestTransport")</sup> and its service `oro_magento.transport.rest_transport` were added. Implements `TransportInterface`, `MagentoTransportInterface`, `ServerTimeAwareInterface`, `PingableInterface`, `LoggerAwareInterface`
This class has the same responsibilities as SoapTransport.
* The next batch jobs were added to `batch_jobs.yml`:
    - mage_store_rest_import
    - mage_website_rest_import
* New channel `magento2` was added to `channels.yml`
* Interface `RestResponseConverterInterface` was added. Class `ResponseConvertersPass`<sup>[[?]](https://github.com/oroinc/crm/tree/2.3.0/src/Oro/Bundle/MagentoBundle/DependencyInjection/Compiler/ResponseConvertersPass.php "Oro\Bundle\MagentoBundle\DependencyInjection\Compiler\ResponseConvertersPass")</sup> was added. Collects converters that implement `RestResponseConverterInterface`
* Processes `magento_soap_schedule_integration` and `magento_rest_schedule_integration` were added
* Class EventDispatchableRestClientFactory was added. It extends the basic factory functionality with an event which can be used to decorate REST client or replace it.
* Interface Oro/Bundle/IntegrationBundle/Provider/Rest/Transport/RestTransportSettingsInterface was added. The purpose of RestTransportSettingsInterface interface is to provide settings required for REST client initialization and are used in factories.
* Event Oro\Bundle\IntegrationBundle\Event\ClientCreatedAfterEvent was added. It is an event which is called when a new client is created. Use it if you want to decorate or replace a client in case of irregular behavior.
* Class Oro\Bundle\IntegrationBundle\EventListener\AbstractClientDecoratorListener was added. It is used by Oro\Bundle\IntegrationBundle\EventListener\LoggerClientDecoratorListener and Oro\Bundle\IntegrationBundle\EventListener\MultiAttemptsClientDecoratorListener. These listeners decorate the client entity after it was created.
* Trait Oro\Bundle\IntegrationBundle\Utils\MultiAttemptsConfigTrait was added. It is used in Oro/Bundle/MagentoBundle/Provider/Transport/SoapTransport and Oro\Bundle\IntegrationBundle\EventListener\MultiAttemptsClientDecoratorListener to execute the feature several times, if the process fails after the first try.


### Changed
#### MagentoBundle
* Support for data synchronization with Magento 2 by REST protocol was added. Store, website and regions dictionaries are available for synchronization. However, synchronization of other entities has not yet been developed and it is, therefore, not available in the current version of the package. This is the reason for Magento 2 integration being absent from the "Channel type" field when creating a new channel.
For more details on how to enable such integration, see [Magento 2 Documentation](src/Oro/Bundle/MagentoBundle/Resources/doc/reference/magento2.md).
* Class `Oro\Bundle\MagentoBundle\Entity\MagentoSoapTransport' was changed. Now it consists of fields and methods that are specific for SoapTransport.
* Class `CustomerIconProvider`<sup>[[?]](https://github.com/oroinc/crm/tree/2.3.0/src/Oro/Bundle/MagentoBundle/Provider/Customer/CustomerIconProvider.php "Oro\Bundle\MagentoBundle\Provider\Customer\CustomerIconProvider")</sup>. Its service was renamed to `oro_magento.provider.customer.magento_customer_icon`
* Class `IntegrationAwareSearchHandler`<sup>[[?]](https://github.com/oroinc/crm/tree/2.3.0/src/Oro/Bundle/MagentoBundle/Autocomplete/IntegrationAwareSearchHandler.php "Oro\Bundle\MagentoBundle\Autocomplete\IntegrationAwareSearchHandler")</sup>
    * method `setSecurityFacade` was replaced with `setAuthorizationChecker`
* Class `NewsletterSubscriberPermissionProvider`<sup>[[?]](https://github.com/oroinc/crm/tree/2.3.0/src/Oro/Bundle/MagentoBundle/Datagrid/NewsletterSubscriberPermissionProvider.php "Oro\Bundle\MagentoBundle\Datagrid\NewsletterSubscriberPermissionProvider")</sup>
    * method `setSecurityFacade` was replaced with `setAuthorizationChecker`
    
    
### Removed
#### MagentoBundle
* Class `ChannelType`<sup>[[?]](https://github.com/oroinc/crm/tree/2.3.0/src/Oro/Bundle/MagentoBundle/Provider/ChannelType.php "Oro\Bundle\MagentoBundle\Provider\ChannelType")</sup> was removed. Logic was moved to `MagentoChannelType`<sup>[[?]](https://github.com/oroinc/crm/tree/2.3.0/src/Oro/Bundle/MagentoBundle/Provider/MagentoChannelType.php "Oro\Bundle\MagentoBundle\Provider\MagentoChannelType")</sup> and its service was renamed to `oro_magento.provider.magento_channel_type`
* Class `StoresSoapIterator`<sup>[[?]](https://github.com/oroinc/crm/tree/2.3.0/src/Oro/Bundle/MagentoBundle/Provider/Iterator/StoresSoapIterator.php "Oro\Bundle\MagentoBundle\Provider\Iterator\StoresSoapIterator")</sup> was removed. Logic was moved to `StoresSoapIterator`<sup>[[?]](https://github.com/oroinc/crm/tree/2.3.0/src/Oro/Bundle/MagentoBundle/Provider/Iterator/Soap/StoresSoapIterator.php "Oro\Bundle\MagentoBundle\Provider\Iterator\Soap\StoresSoapIterator")</sup>:
    * constant `ALL_WEBSITES` moved to `Website`<sup>[[?]](https://github.com/oroinc/crm/tree/2.3.0/src/Oro/Bundle/MagentoBundle/Entity/Website.php "Oro\Bundle\MagentoBundle\Entity\Website")</sup>
    * constant `ADMIN_WEBSITE_ID` moved to `Website`<sup>[[?]](https://github.com/oroinc/crm/tree/2.3.0/src/Oro/Bundle/MagentoBundle/Entity/Website.php "Oro\Bundle\MagentoBundle\Entity\Website")</sup>
    * constant `ADMIN_STORE_ID` moved to `Store`<sup>[[?]](https://github.com/oroinc/crm/tree/2.3.0/src/Oro/Bundle/MagentoBundle/Entity/Store.php "Oro\Bundle\MagentoBundle\Entity\Store")</sup>
* Class `MagentoSoapTransportRepository`<sup>[[?]](https://github.com/oroinc/crm/tree/2.3.0/src/Oro/Bundle/MagentoBundle/Entity/Repository/MagentoSoapTransportRepository.php "Oro\Bundle\MagentoBundle\Entity\Repository\MagentoSoapTransportRepository")</sup> was removed. Logic was moved to `MagentoTransportRepository`<sup>[[?]](https://github.com/oroinc/crm/tree/2.3.0/src/Oro/Bundle/MagentoBundle/Entity/Repository/MagentoTransportRepository.php "Oro\Bundle\MagentoBundle\Entity\Repository\MagentoTransportRepository")</sup>
* Class `SoapConnectorsFormSubscriber`<sup>[[?]](https://github.com/oroinc/crm/tree/2.3.0/src/Oro/Bundle/MagentoBundle/Form/EventListener/SoapConnectorsFormSubscriber.php "Oro\Bundle\MagentoBundle\Form\EventListener\SoapConnectorsFormSubscriber")</sup> was removed. Logic was moved to `ConnectorsFormSubscriber`<sup>[[?]](https://github.com/oroinc/crm/tree/2.3.0/src/Oro/Bundle/MagentoBundle/Form/EventListener/ConnectorsFormSubscriber.php "Oro\Bundle\MagentoBundle\Form\EventListener\ConnectorsFormSubscriber")</sup>
    * added protected method `getFormChannelType`
* Class `SoapSettingsFormSubscriber`<sup>[[?]](https://github.com/oroinc/crm/tree/2.3.0/src/Oro/Bundle/MagentoBundle/Form/EventListener/SoapSettingsFormSubscriber.php "Oro\Bundle\MagentoBundle\Form\EventListener\SoapSettingsFormSubscriber")</sup> was removed. Logic was moved to `SettingsFormSubscriber`<sup>[[?]](https://github.com/oroinc/crm/tree/2.3.0/src/Oro/Bundle/MagentoBundle/Form/EventListener/SettingsFormSubscriber.php "Oro\Bundle\MagentoBundle\Form\EventListener\SettingsFormSubscriber")</sup> and its service were renamed to `oro_magento.form.subscriber.transport_setting`
    * protected method `getModifierWebsitesList` was renamed to `modifyWebsitesList` and now it returns void.
* Class `SoapTransportCheckButtonType`<sup>[[?]](https://github.com/oroinc/crm/tree/2.3.0/src/Oro/Bundle/MagentoBundle/Form/Type/SoapTransportCheckButtonType.php "Oro\Bundle\MagentoBundle\Form\Type\SoapTransportCheckButtonType")</sup> was removed. Logic was moved to `TransportCheckButtonType`<sup>[[?]](https://github.com/oroinc/crm/tree/2.3.0/src/Oro/Bundle/MagentoBundle/Form/Type/TransportCheckButtonType.php "Oro\Bundle\MagentoBundle\Form\Type\TransportCheckButtonType")</sup>
* Method `getSores` in `CartExpirationProcessor`<sup>[[?]](https://github.com/oroinc/crm/tree/2.3.0/src/Oro/Bundle/MagentoBundle/Provider/CartExpirationProcessor.php "Oro\Bundle\MagentoBundle\Provider\CartExpirationProcessor")</sup> was removed. Logic was moved to `getStores` method
* Class `AbstractMagentoConnector`<sup>[[?]](https://github.com/oroinc/crm/tree/2.3.0/src/Oro/Bundle/MagentoBundle/Provider/AbstractMagentoConnector.php "Oro\Bundle\MagentoBundle\Provider\AbstractMagentoConnector")</sup> was removed. Logic was moved to `AbstractMagentoConnector`<sup>[[?]](https://github.com/oroinc/crm/tree/2.3.0/src/Oro/Bundle/MagentoBundle/Connector/AbstractMagentoConnector.php "Oro\Bundle\MagentoBundle\Connector\AbstractMagentoConnector")</sup>
* Class `CartConnector`<sup>[[?]](https://github.com/oroinc/crm/tree/2.3.0/src/Oro/Bundle/MagentoBundle/Provider/CartConnector.php "Oro\Bundle\MagentoBundle\Provider\CartConnector")</sup> was removed. Logic was moved to `Oro\Bundle\MagentoBundle\Connector\CartConnector
* Class `OrderConnector`<sup>[[?]](https://github.com/oroinc/crm/tree/2.3.0/src/Oro/Bundle/MagentoBundle/Provider/OrderConnector.php "Oro\Bundle\MagentoBundle\Provider\OrderConnector")</sup> was removed. Logic was moved to `OrderConnector`<sup>[[?]](https://github.com/oroinc/crm/tree/2.3.0/src/Oro/Bundle/MagentoBundle/Connector/OrderConnector.php "Oro\Bundle\MagentoBundle\Connector\OrderConnector")</sup>
* Class `RegionConnector`<sup>[[?]](https://github.com/oroinc/crm/tree/2.3.0/src/Oro/Bundle/MagentoBundle/Provider/RegionConnector.php "Oro\Bundle\MagentoBundle\Provider\RegionConnector")</sup> was removed. Logic was moved to `RegionConnector`<sup>[[?]](https://github.com/oroinc/crm/tree/2.3.0/src/Oro/Bundle/MagentoBundle/Connector/RegionConnector.php "Oro\Bundle\MagentoBundle\Connector\RegionConnector")</sup>
* Class `CustomerConnector`<sup>[[?]](https://github.com/oroinc/crm/tree/2.3.0/src/Oro/Bundle/MagentoBundle/Provider/CustomerConnector.php "Oro\Bundle\MagentoBundle\Provider\CustomerConnector")</sup> was removed. Logic was moved to `CustomerConnector`<sup>[[?]](https://github.com/oroinc/crm/tree/2.3.0/src/Oro/Bundle/MagentoBundle/Connector/CustomerConnector.php "Oro\Bundle\MagentoBundle\Connector\CustomerConnector")</sup>
* Class `NewsletterSubscriberConnector`<sup>[[?]](https://github.com/oroinc/crm/tree/2.3.0/src/Oro/Bundle/MagentoBundle/Provider/NewsletterSubscriberConnector.php "Oro\Bundle\MagentoBundle\Provider\NewsletterSubscriberConnector")</sup> was removed. Logic was moved to `NewsletterSubscriberConnector`<sup>[[?]](https://github.com/oroinc/crm/tree/2.3.0/src/Oro/Bundle/MagentoBundle/Connector/NewsletterSubscriberConnector.php "Oro\Bundle\MagentoBundle\Connector\NewsletterSubscriberConnector")</sup>
* Class `MagentoConnectorInterface`<sup>[[?]](https://github.com/oroinc/crm/tree/2.3.0/src/Oro/Bundle/MagentoBundle/Provider/MagentoConnectorInterface.php "Oro\Bundle\MagentoBundle\Provider\MagentoConnectorInterface")</sup> was removed. Logic was moved to `MagentoConnectorInterface`<sup>[[?]](https://github.com/oroinc/crm/tree/2.3.0/src/Oro/Bundle/MagentoBundle/Connector/MagentoConnectorInterface.php "Oro\Bundle\MagentoBundle\Connector\MagentoConnectorInterface")</sup>
* Class `AbstractLoadeableSoapIterator`<sup>[[?]](https://github.com/oroinc/crm/tree/2.3.0/src/Oro/Bundle/MagentoBundle/Provider/Iterator/AbstractLoadeableSoapIterator.php "Oro\Bundle\MagentoBundle\Provider\Iterator\AbstractLoadeableSoapIterator")</sup> was removed. Logic was moved to `AbstractLoadeableIterator`<sup>[[?]](https://github.com/oroinc/crm/tree/2.3.0/src/Oro/Bundle/MagentoBundle/Provider/Iterator/AbstractLoadeableIterator.php "Oro\Bundle\MagentoBundle\Provider\Iterator\AbstractLoadeableIterator")</sup>
* Class `AbstractPageableSoapIterator`<sup>[[?]](https://github.com/oroinc/crm/tree/2.3.0/src/Oro/Bundle/MagentoBundle/Provider/Iterator/AbstractPageableSoapIterator.php "Oro\Bundle\MagentoBundle\Provider\Iterator\AbstractPageableSoapIterator")</sup> was removed. Logic was moved to `AbstractPageableIterator`<sup>[[?]](https://github.com/oroinc/crm/tree/2.3.0/src/Oro/Bundle/MagentoBundle/Provider/Iterator/AbstractPageableIterator.php "Oro\Bundle\MagentoBundle\Provider\Iterator\AbstractPageableIterator")</sup>
* Class `AbstractBridgeIterator`<sup>[[?]](https://github.com/oroinc/crm/tree/2.3.0/src/Oro/Bundle/MagentoBundle/Provider/Iterator/AbstractBridgeIterator.php "Oro\Bundle\MagentoBundle\Provider\Iterator\AbstractBridgeIterator")</sup> was removed. Logic was moved to `AbstractBridgeIterator`<sup>[[?]](https://github.com/oroinc/crm/tree/2.3.0/src/Oro/Bundle/MagentoBundle/Provider/Iterator/Soap/AbstractBridgeIterator.php "Oro\Bundle\MagentoBundle\Provider\Iterator\Soap\AbstractBridgeIterator")</sup>
* Class `CartsBridgeIterator`<sup>[[?]](https://github.com/oroinc/crm/tree/2.3.0/src/Oro/Bundle/MagentoBundle/Provider/Iterator/CartsBridgeIterator.php "Oro\Bundle\MagentoBundle\Provider\Iterator\CartsBridgeIterator")</sup> was removed. Logic was moved to `CartsBridgeIterator`<sup>[[?]](https://github.com/oroinc/crm/tree/2.3.0/src/Oro/Bundle/MagentoBundle/Provider/Iterator/Soap/CartsBridgeIterator.php "Oro\Bundle\MagentoBundle\Provider\Iterator\Soap\CartsBridgeIterator")</sup>
* Class `CustomerBridgeIterator`<sup>[[?]](https://github.com/oroinc/crm/tree/2.3.0/src/Oro/Bundle/MagentoBundle/Provider/Iterator/CustomerBridgeIterator.php "Oro\Bundle\MagentoBundle\Provider\Iterator\CustomerBridgeIterator")</sup> was removed. Logic was moved to `CustomerBridgeIterator`<sup>[[?]](https://github.com/oroinc/crm/tree/2.3.0/src/Oro/Bundle/MagentoBundle/Provider/Iterator/Soap/CustomerBridgeIterator.php "Oro\Bundle\MagentoBundle\Provider\Iterator\Soap\CustomerBridgeIterator")</sup>
* Class `CustomerSoapIterator`<sup>[[?]](https://github.com/oroinc/crm/tree/2.3.0/src/Oro/Bundle/MagentoBundle/Provider/Iterator/CustomerSoapIterator.php "Oro\Bundle\MagentoBundle\Provider\Iterator\CustomerSoapIterator")</sup> was removed. Logic was moved to `CustomerSoapIterator`<sup>[[?]](https://github.com/oroinc/crm/tree/2.3.0/src/Oro/Bundle/MagentoBundle/Provider/Iterator/Soap/CustomerSoapIterator.php "Oro\Bundle\MagentoBundle\Provider\Iterator\Soap\CustomerSoapIterator")</sup>
* Class `CustomerGroupBridgeIterator`<sup>[[?]](https://github.com/oroinc/crm/tree/2.3.0/src/Oro/Bundle/MagentoBundle/Provider/Iterator/CustomerGroupBridgeIterator.php "Oro\Bundle\MagentoBundle\Provider\Iterator\CustomerGroupBridgeIterator")</sup> was removed. Logic was moved to `CustomerGroupBridgeIterator`<sup>[[?]](https://github.com/oroinc/crm/tree/2.3.0/src/Oro/Bundle/MagentoBundle/Provider/Iterator/Soap/CustomerGroupBridgeIterator.php "Oro\Bundle\MagentoBundle\Provider\Iterator\Soap\CustomerGroupBridgeIterator")</sup>
* Class `NewsletterSubscriberBridgeIterator`<sup>[[?]](https://github.com/oroinc/crm/tree/2.3.0/src/Oro/Bundle/MagentoBundle/Provider/Iterator/NewsletterSubscriberBridgeIterator.php "Oro\Bundle\MagentoBundle\Provider\Iterator\NewsletterSubscriberBridgeIterator")</sup> was removed. Logic was moved to `NewsletterSubscriberBridgeIterator`<sup>[[?]](https://github.com/oroinc/crm/tree/2.3.0/src/Oro/Bundle/MagentoBundle/Provider/Iterator/Soap/NewsletterSubscriberBridgeIterator.php "Oro\Bundle\MagentoBundle\Provider\Iterator\Soap\NewsletterSubscriberBridgeIterator")</sup> and now implements `NewsletterSubscriberBridgeIteratorInterface`
* Class `OrderBridgeIterator`<sup>[[?]](https://github.com/oroinc/crm/tree/2.3.0/src/Oro/Bundle/MagentoBundle/Provider/Iterator/OrderBridgeIterator.php "Oro\Bundle\MagentoBundle\Provider\Iterator\OrderBridgeIterator")</sup> was removed. Logic was moved to `OrderBridgeIterator`<sup>[[?]](https://github.com/oroinc/crm/tree/2.3.0/src/Oro/Bundle/MagentoBundle/Provider/Iterator/Soap/OrderBridgeIterator.php "Oro\Bundle\MagentoBundle\Provider\Iterator\Soap\OrderBridgeIterator")</sup>
* Class `OrderSoapIterator`<sup>[[?]](https://github.com/oroinc/crm/tree/2.3.0/src/Oro/Bundle/MagentoBundle/Provider/Iterator/OrderSoapIterator.php "Oro\Bundle\MagentoBundle\Provider\Iterator\OrderSoapIterator")</sup> was removed. Logic was moved to `OrderSoapIterator`<sup>[[?]](https://github.com/oroinc/crm/tree/2.3.0/src/Oro/Bundle/MagentoBundle/Provider/Iterator/Soap/OrderSoapIterator.php "Oro\Bundle\MagentoBundle\Provider\Iterator\Soap\OrderSoapIterator")</sup>
* Class `RegionSoapIterator`<sup>[[?]](https://github.com/oroinc/crm/tree/2.3.0/src/Oro/Bundle/MagentoBundle/Provider/Iterator/RegionSoapIterator.php "Oro\Bundle\MagentoBundle\Provider\Iterator\RegionSoapIterator")</sup> was removed. Logic was moved to `RegionSoapIterator`<sup>[[?]](https://github.com/oroinc/crm/tree/2.3.0/src/Oro/Bundle/MagentoBundle/Provider/Iterator/Soap/RegionSoapIterator.php "Oro\Bundle\MagentoBundle\Provider\Iterator\Soap\RegionSoapIterator")</sup>
    * protected method `findEntitiesToProcess()` was moved to parent class
    * protected method `getEntityIds()` was moved to parent class
    * protected method `getEntity($id)` was moved to parent class
    * protected method `getIdFieldName()` was moved to parent class
    * protected method `current()` was moved to parent class
* Class `WebsiteSoapIterator`<sup>[[?]](https://github.com/oroinc/crm/tree/2.3.0/src/Oro/Bundle/MagentoBundle/Provider/Iterator/WebsiteSoapIterator.php "Oro\Bundle\MagentoBundle\Provider\Iterator\WebsiteSoapIterator")</sup> was removed. Logic was moved to `WebsiteSoapIterator`<sup>[[?]](https://github.com/oroinc/crm/tree/2.3.0/src/Oro/Bundle/MagentoBundle/Provider/Iterator/Soap/WebsiteSoapIterator.php "Oro\Bundle\MagentoBundle\Provider\Iterator\Soap\WebsiteSoapIterator")</sup>
* Interface `MagentoTransportInterface`<sup>[[?]](https://github.com/oroinc/crm/tree/2.3.0/src/Oro/Bundle/MagentoBundle/Provider/Transport/MagentoTransportInterface.php "Oro\Bundle\MagentoBundle\Provider\Transport\MagentoTransportInterface")</sup>
    * removed the `call` method because it conflicts with REST conception. From now on, MagentoTransportInterface will not allow to specify http methods and resource through parameters.
* Route `oro_magento_soap_check` was renamed to `oro_magento_integration_check`
* Translation with key `not_valid_parameters` was removed
* Process `magento_schedule_integration` was removed.


## 2.2.0 (2017-05-31)
[Show detailed list of changes](#incompatibilities-2-2.md)

### Changed
#### SalesBundle
* Implementation of REST API for customer association was changed.
    * the logic related to the customer association is implemented in `CustomerAssociationListener`<sup>[[?]](https://github.com/oroinc/crm/blob/2.2/src/Oro/Bundle/SalesBundle/Api/Form/EventListener/CustomerAssociationListener.php "Oro\Bundle\SalesBundle\Api\Form\EventListener\CustomerAssociationListener")</sup>
    
### Removed
#### SalesBundle
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
    
    
## 2.1.0 (2017-03-30)
[Show detailed list of changes](#incompatibilities-2-1.md)

### Changed
#### AnalyticsBundle
* Class `RFMBuilder`<sup>[[?]](https://github.com/oroinc/crm/tree/2.1.0/src/Oro/Bundle/AnalyticsBundle/Builder/RFMBuilder.php "Oro\Bundle\AnalyticsBundle\Builder\RFMBuilder")</sup>
    * changed the return type of `getEntityIdsByChannel` method from `\ArrayIterator|BufferedQueryResultIterator` to `\Iterator`
#### CRMBundle
* Updated service definition for `oro_crm.extension.transtation_packages_provider`:
    * changed class to `TranslationPackagesProviderExtension`<sup>[[?]](https://github.com/oroinc/crm/tree/2.1.0/src/Oro/Bundle/FrontendBundle/Provider/TranslationPackagesProviderExtension.php "Oro\Bundle\FrontendBundle\Provider\TranslationPackagesProviderExtension")</sup>
    * changed publicity to `false
#### ChannelBundle
* Class `RecalculateLifetimeCommand`<sup>[[?]](https://github.com/oroinc/crm/tree/2.1.0/src/Oro/Bundle/ChannelBundle/Command/RecalculateLifetimeCommand.php "Oro\Bundle\ChannelBundle\Command\RecalculateLifetimeCommand")</sup>
    * changed the return type of `getCustomersIterator` method from `BufferedQueryResultIterator` to `BufferedQueryResultIteratorInterface`
* Class `AccountLifetimeSubscriber`<sup>[[?]](https://github.com/oroinc/crm/tree/2.1.0/src/Oro/Bundle/ChannelBundle/EventListener/AccountLifetimeSubscriber.php "Oro\Bundle\ChannelBundle\EventListener\AccountLifetimeSubscriber")</sup>
    * changed the return type of `getCustomersIterator` method from `BufferedQueryResultIterator` to `BufferedQueryResultIteratorInterface`
* The following services were marked as `private`:
    * `oro_channel.twig.metadata_extension`
    * `oro_channel.twig.lifetime_value_extension`
#### ContactBundle
* The service `oro_contact.twig.extension.social_url` was renamed to `oro_contact.twig.extension` and marked as `private`
#### SalesBundle
* Class `AccountExtension`<sup>[[?]](https://github.com/oroinc/crm/tree/2.1.0/src/Oro/Bundle/SalesBundle/Datagrid/Extension/Customers/AccountExtension.php "Oro\Bundle\SalesBundle\Datagrid\Extension\Customers\AccountExtension")</sup>:
    * added UnsupportedGridPrefixesTrait
* opportunity` and `lead` apis changed:
    * `customerAssociation` relation replaced by `customer` and `account` relations
    
    
### Deprecated
#### SalesBundle
* Class `OpportunityRepository`<sup>[[?]](https://github.com/oroinc/crm/tree/2.1.0/src/Oro/Bundle/SalesBundle/Entity/Repository/OpportunityRepository.php "Oro\Bundle\SalesBundle\Entity\Repository\OpportunityRepository")</sup>:
    * Method `getNewOpportunitiesAmount` was marked as deprecated. Method `getOpportunitiesByPeriodQB` should be used instead
    * Method `getWonOpportunitiesToDateCount` was marked as deprecated. Method `getWonOpportunitiesCountByPeriodQB` should be used instead
    
    
### Removed
#### ChannelBundle
* Removed the following parameters from DIC:
    * `oro_channel.twig.metadata_extension.class`
    * `oro_channel.twig.lifetime_value_extension.class`
#### ContactBundle
* Removed the following parameters from DIC:
    * `oro_contact.twig.extension.social_url.class`
* Class `SocialUrlExtension`<sup>[[?]](https://github.com/oroinc/crm/tree/2.1.0/src/Oro/Bundle/ContactBundle/Twig/SocialUrlExtension.php "Oro\Bundle\ContactBundle\Twig\SocialUrlExtension")</sup> was renamed to `ContactExtension`<sup>[[?]](https://github.com/oroinc/crm/tree/2.1.0/src/Oro/Bundle/ContactBundle/Twig/ContactExtension.php "Oro\Bundle\ContactBundle\Twig\ContactExtension")</sup>


## 2.0.0 (2017-01-16)

 * Changed minimum required php version to 5.6
 * Added support of PHP 7.1

## 1.10.0


 * The application has been upgraded to Symfony 2.8 (Symfony 2.8.10 is not supported because of [Symfony issue](https://github.com/symfony/symfony/issues/19840))
 * Added support of php 7
 * Changed minimum required php version to 5.5.9

## 1.9.0 (2016-02-15)

 * Filter records by teams
 * Pipeline forecast widget and report
 * Contexts for all activities
 * Account activities
 * Unread email widget for the sidebar panel
 * Activities are available in the Merge Accounts dialog, allowing you to specify merge strategy for every type of activity
 * Filter emails by correspondent on the My Emails page
 * Segment Magento customers by coupons and discounts applied to their orders

## 1.8.0 (2015-08-26)

 * Improved Email capabilities and features
 * Email automation
 * Contact history and last contact date is tracked for all records, allowing to segment them based on number of contacts, or date or direction of last contact
 * Tags may be used as filtering conditions in segments and grids
 * UX for Ecommerce Statistics widget has been improved

## 1.7.0 (2015-04-28)

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

## 1.6.0 (2015-01-19)

 * Availability of email campaign results for filtering in reports & segments.
Now email campaign results, such as opens, clicks, bounces, etc., are available for filter queries in reporting and customer segmentation. This also includes campaign statistics received via MailChimp integration

## 1.5.0 (2014-12-18)

### RFM analytic for Magento channels

RFM is a popular set of metrics used to analyze customer value and to determine the best customers, especially for retail and e-commerce. The 1.5.0 release of OroCRM adds the ability to configure RFM metrics for Magento channels.

The RFM score consists of three metrics:
 - Recency, that evaluates the number of days that passed since the last purchase. The more recent is the purchase, the better.
 - Frequency, that evaluates the number of orders placed by the customer in the last 365 days. The more frequently the customer buys, the better.
 - Monetary value, that evaluates the total amount of orders placed by the customer in the last 365 days. The more money customer spends, the better.
To construct these metrics, the entire range of values is divided into a small number of categories, or "buckets." The number of buckets usually lies in range of 3 to 10, and scores for R, F, and M range accordingly—from 1 (the best score) to the maximum number of buckets (the worst score). You can change the number of buckets and move their boundaries in order to adjust the scores to characteristic values of your business.
After the metric is applied, every customer gets a three-number set of RFM scores. R1 F1 M1 identifies the best customers, and the higher the scores are, the worse these customers perform in a particular field.
RFM scores are displayed on the Magento customer view page and on the customer section of the Account view. You may also re-use these scores in reporting and segmentation.

## 1.4.0 (2014-10-15)

### The re-introduction of Channels

We started the implementation of a new vision for the Channels in 1.3 version and now we bring Channels back, although under a new definition.
The general idea behind channels may be explained as follows: a channel in OroCRM represents an outside source customer and sales data, where "customer" and "sales" must be understood in the broadest sense possible. Depending on the nature of the outside source, the channel may or may not require a data integration.
This new definition leads to multiple noticeable changes across the system.

### Accounts
 
Account entity now performs as the "umbrella" entity for all customer identities across multiple channels, displaying all their data in a single view.

### Integration management
 
Albeit the Integrations grid still displays all integrations that exist in the system, you now may create only "non-customer" standalone integrations, such as Zendesk integration. The "customer" integrations, such as Magento integration, may be created only in scope of a channel and cannot exist without it.

### Channel management UI
 
The UI for channel creation now allows the user to specify channel type. By default there are three channel types: Magento, B2B, and Custom; more channel types may be created by developers.

Each channel type characterizes the following:

* Whether a channel requires an integration. If the answer is yes (cf. Magento), the integration should be configured along the creation of the channel.
* Which entity will serve as the Customer Identity. This entity cannot be changed by the user.
* Which entities will be enabled in the system along with the channel.
A specific set of entities comes by default (e.g. Sales Process, Lead, and Opportunity for B2B channel), but the user may remove or add entities if necessary.

### B2B functionality
 
B2B functionality, such as Leads or Opportunities will no longer be available by default—in order to work with them the user should create at least one B2B channel first. As a result it is now possible to configure your instance of OroCRM to be fully B2C-oriented and work only with entities that make sense in eCommerce context—with no mandatory Leads and Opportunities at all.

In order to comply with the new concept of Customer Identity, the new entity named B2B Customer was added to the system. It replaces Account in most cases of our default Sales Process workflows.

### Lifetime sales value

This feature provides the means to record historical sales for every channel type. The exact definition of what constitutes sales is subject to channel type: for Magento channels lifetime sales are counted as order subtotal (excluding cancelled orders), and for B2B channels it is counted as total value of won opportunities. The common metric allows you to quickly compare sales across channels in the account view, where both per-channel and account total values are displayed.

### Marketing lists

Marketing lists serve as the basis for marketing activities, such as email campaigns (see below). They represent a target auditory of the activity—that is, people, who will be contacted when the activity takes place. Marketing lists have little value by themselves; they exist in scope of some marketing campaign and its activities.

Essentially, marketing list is a segment of entities that contain some contact information, such as email or phone number or physical address. Lists are build based on some rules using Oro filtering tool. Similarly to segments, marketing lists can be static or dynamic; the rules are the same. The user can build marketing lists of contacts, Magento customers, leads, etc.

In addition to filtering rules, the user can manually tweak contents of the marketing list by removing items ("subscribers") from it. Removed subscribers will no longer appear in the list even if they fit the conditions. It is possible to move them back in the list, too.

Every subscriber can also unsubscribe from the list. In this case, he will remain in the list, but will no longer receive email campaigns that are sent to this list. Note that subscription status is managed on per-list basis; the same contact might be subscribed to one list and unsubscribed from another.

### Email campaigns

Email campaign is a first example of marketing activity implemented in OroCRM. The big picture is following: Every marketing campaign might contain multiple marketing activities, e.g. an email newsletter, a context ad campaign, a targeted phone advertisement. All these activities serve the common goal of the "big" marketing campaign.

In its current implementation, email campaign is a one-time dispatch of an email to a list of subscribers. Hence, the campaign consists of three basic parts:

Recipients—represented by a Marketing list.
Email itself—the user may choose a template, or create a campaign email from scratch.
Sending rules—for now, only one-time dispatch is available.
Email campaign might be tied to a marketing campaign, but it might exist on its own as well.

### Ecommerce dashboard

In addition to default dashboard we have added a special Ecommerce-targeted board with three widgets:
* Average order amount
* New web customers
* Average customer lifetime sales

Every widget displays historical trend for the particular value over the past 12 months. You can also add them to any other dashboard using the Add Widget button.

### The re-introduction of Channels

We started the implementation of a new vision for the Channels in 1.3 version and now we bring Channels back, although under a new definition.
The general idea behind channels may be explained as follows: a channel in OroCRM represents an outside source customer and sales data, where "customer" and "sales" must be understood in the broadest sense possible. Depending on the nature of the outside source, the channel may or may not require a data integration.
This new definition leads to multiple noticeable changes across the system.

### Accounts

Account entity now performs as the "umbrella" entity for all customer identities across multiple channels, displaying all their data in a single view.

### Integration management

Albeit the Integrations grid still displays all integrations that exist in the system, you now may create only "non-customer" standalone integrations, such as Zendesk integration. The "customer" integrations, such as Magento integration, may be created only in scope of a channel and cannot exist without it.

### Channel management UI

The UI for channel creation now allows the user to specify channel type. By default there are three channel types: Magento, B2B, and Custom; more channel types may be created by developers.

Each channel type characterizes the following:

* Whether a channel requires an integration. If the answer is yes (cf. Magento), the integration should be configured along the creation of the channel.
* Which entity will serve as the Customer Identity. This entity cannot be changed by the user.
* Which entities will be enabled in the system along with the channel.
* A specific set of entities comes by default (e.g. Sales Process, Lead, and Opportunity for B2B channel), but the user may remove or add entities if necessary.

### B2B functionality

B2B functionality, such as Leads or Opportunities will no longer be available by default—in order to work with them the user should create at least one B2B channel first. As a result it is now possible to configure your instance of OroCRM to be fully B2C-oriented and work only with entities that make sense in eCommerce context—with no mandatory Leads and Opportunities at all.
In order to comply with the new concept of Customer Identity, the new entity named B2B Customer was added to the system. It replaces Account in most cases of our default Sales Process workflows.

### Lifetime sales value

This feature provides the means to record historical sales for every channel type. The exact definition of what constitutes sales is subject to channel type: for Magento channels lifetime sales are counted as order subtotal (excluding cancelled orders), and for B2B channels it is counted as total value of won opportunities. The common metric allows you to quickly compare sales across channels in the account view, where both per-channel and account total values are displayed.

### Marketing lists

Marketing lists serve as the basis for marketing activities, such as email campaigns (see below). They represent a target auditory of the activity—that is, people, who will be contacted when the activity takes place. Marketing lists have little value by themselves; they exist in scope of some marketing campaign and its activities.

Essentially, marketing list is a segment of entities that contain some contact information, such as email or phone number or physical address. Lists are build based on some rules using Oro filtering tool. Similarly to segments, marketing lists can be static or dynamic; the rules are the same. The user can build marketing lists of contacts, Magento customers, leads, etc.

In addition to filtering rules, the user can manually tweak contents of the marketing list by removing items ("subscribers") from it. Removed subscribers will no longer appear in the list even if they fit the conditions. It is possible to move them back in the list, too.

Every subscriber can also unsubscribe from the list. In this case, he will remain in the list, but will no longer receive email campaigns that are sent to this list. Note that subscription status is managed on per-list basis; the same contact might be subscribed to one list and unsubscribed from another.

### Email campaigns
 
Email campaign is a first example of marketing activity implemented in OroCRM. The big picture is following: Every marketing campaign might contain multiple marketing activities, e.g. an email newsletter, a context ad campaign, a targeted phone advertisement. All these activities serve the common goal of the "big" marketing campaign.

In its current implementation, email campaign is a one-time dispatch of an email to a list of subscribers. Hence, the campaign consists of three basic parts:
Recipients—represented by a Marketing list.
Email itself—the user may choose a template, or create a campaign email from scratch.
Sending rules—for now, only one-time dispatch is available.
Email campaign might be tied to a marketing campaign, but it might exist on its own as well.

### Ecommerce dashboard

In addition to default dashboard we have added a special Ecommerce-targeted board with three widgets:

* Average order amount
* New web customers
* Average customer lifetime sales

Every widget displays historical trend for the particular value over the past 12 months. You can also add them to any other dashboard using the Add Widget button.

## 1.3.1 (2014-08-14)

 * Magento Synchronization stabilization improvements
 * Fixed issue: Incorrect row count on grids.
 * Fixed issue: Reports and Segments crash when "Is empty" filter is added.
 * Fixed issue: Recent Emails dashboard widget is broken.
 * Fixed issue: Accounts cannot be linked to Contacts from Edit Contact page.

## 1.3.0 (2014-07-23)

 * Website event tracking
 * Marketing campaigns
 * Campaign code tracking
 * Cases
 * Processes within Magento integration
 * Activities (Notes, Emails, Attachments)
 * Data import in CSV format
 * Zendesk integration
 * Other changes and improvements

## 1.2.0 (2014-05-28)
 * Two-side customer data synchronization with Magento
 * Improvements to Customer view
 * Improvements to Magento data view
 * Fixed issue Broken widgets in merged Account view
 * Fixed Community requests
 * Improvements to Customer view
 * Improvements to display of Magento data
 * Fixed issue Broken widgets in merged Account view

## 1.0.0 (2014-04-01)

 * Tasks
 * Improved UI for launch of the Sales Process workflow
 * Refactored Flexible Workflows
 * Embedded forms
 * Account merging
 * Improved Reports
 * Improved Workflow
 * Improved Dashboard
 * Magento import performance improvements
 * Other improvements in channnels, contacts
 * Magento data import: Customers, Shopping carts and Orders
 * B2C Sales Flow
 * Call view window
 * Basic dashboards
 * Reports creation wizard (Table reports)
 * B2B Sales Flow adjustments
 * Call entity
 * Add weather layer in the map on contact view page
 * Workflow transitions
 * Make all entities as Extended
 * End support for Internet Explorer 9
 * OroPlatform Beta 3 dependency changes
 * OroPlatform Beta 2 dependency changes
 * CRM Entities reports
 * Contacts Import/Export
 * Account association with contacts
 * Custom entities and fields in usage
 * Leads and Opportunities
 * Flexible Workflow Engine (FWE)
 * Contacts Improvements
   * added ability to manage addresses from contact view page with Google Maps API support
   * added support of multiple Emails and Phones for Contact
 * Address Types Management. Added ability to set different type for addresses in Contact address book
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
