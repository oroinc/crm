UPGRADE FROM 2.0 to 2.1
========================

Oro Marketing Bundles
---------------------

###CampaignBundle
- Method `getCampaignsByCloseRevenue` was removed from `Oro\Bundle\CampaignBundle\Entity\Repository\CampaignRepository`.
  Use `Oro\Bundle\CampaignBundle\Dashboard\CampaignDataProvider::getCampaignsByCloseRevenueData` instead

###MarketingListBundle
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
