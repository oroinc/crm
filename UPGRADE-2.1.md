UPGRADE FROM 2.0 to 2.1
========================

#### General
- Changed minimum required php version to 7.0
- Updated dependency to [fxpio/composer-asset-plugin](https://github.com/fxpio/composer-asset-plugin) composer plugin to version 1.3.
- Composer updated to version 1.4.

```
    composer self-update
    composer global require "fxp/composer-asset-plugin"
```

Oro Marketing Bundles
---------------------

### CampaignBundle
- Method `getCampaignsByCloseRevenue` was removed from `Oro\Bundle\CampaignBundle\Entity\Repository\CampaignRepository`.
  Use `Oro\Bundle\CampaignBundle\Dashboard\CampaignDataProvider::getCampaignsByCloseRevenueData` instead

### MarketingListBundle
- Class `Oro\Bundle\MarketingListBundle\Provider\MarketingListProvider`
    - changed the return type of `getMarketingListEntitiesIterator` method from `BufferedQueryResultIterator` to `\Iterator`
- Removed the following parameters from DIC:
    - `oro_marketing_list.twig.extension.contact_information_fields.class`
- The following services were marked as `private`:
    - `oro_marketing_list.twig.extension.contact_information_fields`
- Class `Oro\Bundle\MarketingListBundle\Twig\ContactInformationFieldsExtension`
    - the construction signature of was changed. Now the constructor has only `ContainerInterface $container` parameter
    - removed property `protected $helper`


AnalyticsBundle
---------------
- Class `Oro\Bundle\AnalyticsBundle\Builder\RFMBuilder`
    - changed the return type of `getEntityIdsByChannel` method from `\ArrayIterator|BufferedQueryResultIterator` to `\Iterator`

ChannelBundle
-------------
- Class `Oro\Bundle\ChannelBundle\Command\RecalculateLifetimeCommand`
    - changed the return type of `getCustomersIterator` method from `BufferedQueryResultIterator` to `BufferedQueryResultIteratorInterface`
- Class `Oro\Bundle\ChannelBundle\EventListener\AccountLifetimeSubscriber`
    - the construction signature of was changed. Now the constructor has only `ContainerInterface $container` parameter
    - removed property `protected $accountCustomerManager`
    - removed property `protected $currencyQbTransformer`
    - the visibility of property `$accounts` was changed from `protected` to `private`
    - the visibility of methods `createNoCustomerCondition`, `scheduleOpportunityAccount`, `scheduleCustomerAccounts`, `scheduleAccount` were changed from `protected` to `private`
- Removed the following parameters from DIC:
    - `oro_channel.twig.metadata_extension.class`
    - `oro_channel.twig.lifetime_value_extension.class`
- The following services were marked as `private`:
    - `oro_channel.twig.metadata_extension`
    - `oro_channel.twig.lifetime_value_extension`
- Class `Oro\Bundle\ChannelBundle\Twig\LifetimeValueExtension`
    - the construction signature of was changed. Now the constructor has only `ContainerInterface $container` parameter
    - removed property `protected $amountProvider`
- Class `Oro\Bundle\ChannelBundle\Twig\MetadataExtension`
    - the construction signature of was changed. Now the constructor has only `ContainerInterface $container` parameter
    - removed property `protected $metaDataProvider`
- Class `Oro\Bundle\ChannelBundle\Entity\Repository`
    - signature of `calculateAccountLifetime` method was changed. Now it takes following arguments:
        - `array` $customerIdentities
        - `Account` $account
        - `Channel` $channel = null

ContactBundle
-------------
- Removed the following parameters from DIC:
    - `oro_contact.twig.extension.social_url.class`
- The service `oro_contact.twig.extension.social_url` was renamed to `oro_contact.twig.extension` and marked as `private`
- Class `Oro\Bundle\ContactBundle\Twig\SocialUrlExtension` was renamed to `Oro\Bundle\ContactBundle\Twig\ContactExtension` and the following changes were made:
    - the construction signature of was changed. Now the constructor has only `ContainerInterface $container` parameter
    - removed property `protected $socialUrlFormatter`

SalesBundle
---------
- Class `Oro\Bundle\SalesBundle\Datagrid\Extension\Customers\AccountExtension`
    - removed method `isReportOrSegmentGrid`
    - added UnsupportedGridPrefixesTrait
- Class `Oro\Bundle\SalesBundle\Dashboard\Provider\OpportunityByStatusProvider`
    - construction signature was changed. The parameter `OwnerHelper $ownerHelper` was replaced by `WidgetProviderFilterManager $widgetProviderFilter`
    - property `protected $ownerHelper` was replaced by `protected $widgetProviderFilter`
- Class `Oro\Bundle\SalesBundle\Entity\Repository\LeadRepository`
    - removed method `getLeadsCount`
    - removed method `getNewLeadsCount`
    - removed method `getOpenLeadsCount`
    - added method `getLeadsCountQB` which returns an instance of `Doctrine\ORM\QueryBuilder`
    - added method `getNewLeadsCountQB` which returns an instance of `Doctrine\ORM\QueryBuilder`
    - added method `getOpenLeadsCountQB` which returns an instance of `Doctrine\ORM\QueryBuilder`
- Class `Oro\Bundle\SalesBundle\Entity\Repository\OpportunityRepository`
    - removed method `getOpportunitiesCount`
    - removed method `getNewOpportunitiesCount`
    - added method `getOpportunitiesCountQB` which returns an instance of `Doctrine\ORM\QueryBuilder`
    - added method `getNewOpportunitiesCountQB` which returns an instance of `Doctrine\ORM\QueryBuilder`
    - changed signature of method `createOpportunitiesCountQb` removed parameter `$owners`
    - Method `getNewOpportunitiesAmount` was marked as deprecated. Method `getOpportunitiesByPeriodQB` should be used instead
    - Method `getWonOpportunitiesToDateCount` was marked as deprecated. Method `getWonOpportunitiesCountByPeriodQB` should be used instead
- Class `Oro\Bundle\SalesBundle\Provider\B2bBigNumberProvider`
    - construction signature of was changed. New parameter `WidgetProviderFilterManager $widgetProviderFilter` was added
    - added property `protected $widgetProviderFilter`
    - added property `protected $leadRepository`
    - added property `protected $opportunityRepository`
    - changed signature of method `getLeadsCount`. The parameter `$owners` was replaced by `WidgetOptionBag $widgetOptions`
    - changed signature of method `getNewLeadsCount`. The parameter `$owners` was replaced by `WidgetOptionBag $widgetOptions`
    - changed signature of method `getOpenLeadsCount`. The parameter `$owners` was replaced by `WidgetOptionBag $widgetOptions`
    - changed signature of method `getOpportunitiesCount`. The parameter `$owners` was replaced by `WidgetOptionBag $widgetOptions`
    - changed signature of method `getNewOpportunitiesCount`. The parameter `$owners` was replaced by `WidgetOptionBag $widgetOptions`
    - changed signature of method `getNewOpportunitiesAmount`. The parameter `$owners` was replaced by `WidgetOptionBag $widgetOptions`
    - changed signature of method `getWonOpportunitiesToDateCount`. The parameter `$owners` was replaced by `WidgetOptionBag $widgetOptions`
    - changed signature of method `getWonOpportunitiesToDateAmount`. The parameter `$owners` was replaced by `WidgetOptionBag $widgetOptions`
- Class `Oro\Bundle\SalesBundle\Provider\ForecastOfOpportunities`
    - construction signature was changed. The parameter `OwnerHelper $ownerHelper` was removed
    - removed property `protected $ownerHelper`
- Class `Oro\Bundle\SalesBundle\Provider\Opportunity\ForecastProvider`
    - construction signature was changed. New parameter `WidgetProviderFilterManager $widgetProviderFilter` was added
    - added property `protected $widgetProviderFilter`
    - changed signature of method `getForecastData`. The parameter `$ownerIds` was replaced by `WidgetOptionBag $widgetOptions`
- Class `Oro\Bundle\SalesBundle\Provider\Opportunity\IndeterminateForecastProvider.php`
    - construction signature was changed. The parameter `OwnerHelper $ownerHelper` was replaced by `WidgetProviderFilterManager $widgetProviderFilter`
    - property `protected $ownerHelper` was replaced by `protected $widgetProviderFilter`
- Class `Oro\Bundle\SalesBundle\Api\Processor\CustomerAssociationCustomizeLoadedData`
    - construction signature was changed now it takes next arguments:
        - `ConfigProvider` $configProvider,
        - `DoctrineHelper` $doctrineHelper,
        - $customerAssociationField
- Class `Oro\Bundle\SalesBundle\Form\Type\CustomerApiType`
    - construction signature was changed now it takes next arguments:
        - `AccountCustomerManager` $accountCustomerManager
- `opportunity` and `lead` apis changed
    - `customerAssociation` relation replaced by `customer` and `account` relations

CRMBundle
---------
- Class `Oro\Bundle\CRMBundle\Provider\TranslationPackagesProviderExtension` removed
- Updated service definition for `oro_crm.extension.transtation_packages_provider`
    - changed class to `Oro\Bundle\FrontendBundle\Provider\TranslationPackagesProviderExtension`
    - changed publicity to `false`
