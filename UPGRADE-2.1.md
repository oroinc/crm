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


AnalyticsBundle
---------------
- Class `Oro\Bundle\AnalyticsBundle\Builder\RFMBuilder`
    - changed the return type of `getEntityIdsByChannel` method from `\ArrayIterator|BufferedQueryResultIterator` to `\Iterator`

ChannelBundle
-------------
- Class `Oro\Bundle\ChannelBundle\Command\RecalculateLifetimeCommand`
    - changed the return type of `getCustomersIterator` method from `BufferedQueryResultIterator` to `BufferedQueryResultIteratorInterface`

SalesBundle
---------
- Class `Oro\Bundle\SalesBundle\Datagrid\Extension\Customers\AccountExtension`
    - removed method `isReportOrSegmentGrid`
    - added UnsupportedGridPrefixesTrait
