UPGRADE FROM 1.10 to 2.0 
========================

####OroSalesBundle:
- Removed fields `workflowItem` and `workflowStep` from entity `Oro\Bundle\SalesBundle\Entity\Lead`
- Removed fields `workflowItem` and `workflowStep` from entity `Oro\Bundle\SalesBundle\Entity\Opportunity`
- Removed fields `workflowItem` and `workflowStep` from entity `Oro\Bundle\SalesBundle\Entity\SalesFunnel`
- `OroCRM/Bundle/SalesBundle/Entity/LeadMailboxProcessSettings` extends `Oro\Bundle\SalesBundle\Model\ExtendLeadMailboxProcessSettings`

####OroCaseBundle:
- `OroCRM/Bundle/CaseBundle/Entity/CaseMailboxProcessSettings` extends `Oro\Bundle\CaseBundle\Model\ExtendCaseMailboxProcessSettings`

####OroContactUsBundle:
- Removed fields `workflowItem` and `workflowStep` from entity `Oro\Bundle\ContactUsBundle\Entity\ContactRequest`

####OroMagentoBundle:
- Removed fields `workflowItem` and `workflowStep` from entity `Oro\Bundle\MagentoBundle\Entity\Cart`
- Removed fields `workflowItem` and `workflowStep` from entity `Oro\Bundle\MagentoBundle\Entity\Order`
- The `CartExpirationProcessor` now implements `SyncProcessorInterface`
- The command class `CartExpirationSyncCommand` renamed to `SyncCartExpirationCommand`.
- The `InitialSyncCommand` command arguments were changed to be compatible with SyncCommand ones.
- The `InitialSyncCommand` command option `--integration-id` renamed to `--integration`.
- The `InitialSyncCommand` command option `--skip-dictionary` removed. Use argument skip-dictionary=foo instead.

####OroChannelBundle:
- The event `orocrm_channel.channel.status_change` was removed. Use message queue topic `orocrm_channel.channel.status_change` instead.
- `ChangeIntegrationStatusListener` class was removed.
- `ChannelChangeStatusEvent` was removed.
- The parameter `orocrm_channel.event_listener.change_integration_status.class` was removed.
- The parameter `orocrm_channel.event_listener.timezone_change.class` was removed.
- Channel configuration file now loads from `Resources/config/oro/channels.yml` instead of `Resources/config/channel_configuration.yml`.
- Root node for channel config in `Resources/config/oro/channels.yml` were changed from `orocrm_channel` to `channels`.

###OroMarketingListBundle
- Class `Oro\Bundle\MarketingListBundle\Model\ContactInformationFieldHelper
    - method `getQueryContactInformationColumns` was removed. Use method `getQueryContactInformationFields` instead.
    - method `getEntityContactInformationColumns` `was removed. Use method getEntityContactInformationFields` instead.
    - method `getEntityContactInformationColumnsInfo` was removed. Use method `getEntityContactInformationFieldsInfo` instead.
    - method `getEntityLevelContactInfoColumns` was removed. Use method `getEntityLevelContactInfoFields` instead.

####OroAnalyticsBundle:
- The class `StateManager` and its service `orocrm_analytics.model.state_manager` were removed.
- The method `RFMMetricStateManager::scheduleRecalculation` was removed. Use appropriate method from `ScheduleCalculateAnalyticsService` service.
