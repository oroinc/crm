UPGRADE FROM 1.10 to 2.0 
========================

####OroSalesBundle:
- Removed fields `workflowItem` and `workflowStep` from entity `Oro\Bundle\SalesBundle\Entity\Lead`
- Removed fields `workflowItem` and `workflowStep` from entity `Oro\Bundle\SalesBundle\Entity\Opportunity`
- Removed fields `workflowItem` and `workflowStep` from entity `Oro\Bundle\SalesBundle\Entity\SalesFunnel`
- `OroCRM/Bundle/SalesBundle/Entity/LeadMailboxProcessSettings` extends `Oro\Bundle\SalesBundle\Model\ExtendLeadMailboxProcessSettings`
- Class `Oro\Bundle\SalesBundle\Provider\LeadToOpportunityProvider`
    - construction signature was changed, now it takes the next arguments:
        - `B2bGuesser` $b2bGuesser,
        - `EntityFieldProvider` $entityFieldProvider,
        - `ChangeLeadStatus` $changeLeadStatus
    - method `isDisqualifyAndConvertAllowed` was removed. Use methods `Oro\Bundle\SalesBundle\Provider\LeadActionsAccessProvider::isDisqualifyAllowed` and `Oro\Bundle\SalesBundle\Provider\LeadActionsAccessProvider::isConvertToOpportunityAllowed` instead.
- Changed signature of constructor of `Oro\Bundle\SalesBundle\Form\Type\LeadType` - now it takes the following argument:
      - `EntityAliasResolver $entityAliasResolver`.
- Changed signature of constructor of `Oro\Bundle\SalesBundle\Form\Type\OpportunityType` - now it takes the following arguments:
      - `ProbabilityProvider $probabilityProvider`,
        `EnumValueProvider $enumValueProvider`,
        `EnumTypeHelper $typeHelper`,
        `OpportunityRelationsBuilder $relationsBuilder`,
        `EntityAliasResolver $entityAliasResolver`.
- Changed signature of constructor of `Oro\Bundle\SalesBundle\Provider\LeadToOpportunityProvider` - now it takes the following arguments:
      - `EntityFieldProvider $entityFieldProvider`,
        `ChangeLeadStatus $changeLeadStatus`
- Service (`Oro\Bundle\SalesBundle\Model\B2bGuesser`) removed

####OroCaseBundle:
- `OroCRM/Bundle/CaseBundle/Entity/CaseMailboxProcessSettings` extends `Oro\Bundle\CaseBundle\Model\ExtendCaseMailboxProcessSettings`

####OroContactUsBundle:
- Removed fields `workflowItem` and `workflowStep` from entity `Oro\Bundle\ContactUsBundle\Entity\ContactRequest`

####OroMagentoBundle:
- Removed fields `workflowItem` and `workflowStep` from entity `Oro\Bundle\MagentoBundle\Entity\Cart`
- Removed fields `workflowItem` and `workflowStep` from entity `Oro\Bundle\MagentoBundle\Entity\Order`
- The `Oro\Bundle\MagentoBundle\Provider\CartExpirationProcessor` now implements `Oro\Bundle\IntegrationBundle\Provider\SyncProcessorInterface`
- The class `Oro\Bundle\MagentoBundle\Command\CartExpirationSyncCommand` renamed to `Oro\Bundle\MagentoBundle\Command\SyncCartExpirationCommand`.
- The `Oro\Bundle\MagentoBundle\Command\InitialSyncCommand` command arguments were changed to be compatible with SyncCommand ones.
- The `Oro\Bundle\MagentoBundle\Command\InitialSyncCommand` command option `--integration-id` renamed to `--integration`.
- The `Oro\Bundle\MagentoBundle\Command\InitialSyncCommand` command option `--skip-dictionary` removed. Use  `skip-dictionary=true` for `connector_parameters` argument instead.

####OroChannelBundle:
- The event `orocrm_channel.channel.status_change` was removed. Use the message queue topic `orocrm_channel.channel.status_change` instead.
- The class `Oro\Bundle\ChannelBundle\EventListener\ChangeIntegrationStatusListener` was removed.
- The class `Oro\Bundle\ChannelBundle\Event\ChannelChangeStatusEvent` was removed.
- The parameter `orocrm_channel.event_listener.change_integration_status.class` was removed.
- The parameter `orocrm_channel.event_listener.timezone_change.class` was removed.
- Channel configuration file now loads from `Resources/config/oro/channels.yml` instead of `Resources/config/channel_configuration.yml`.
- Root node for channel config in `Resources/config/oro/channels.yml` were changed from `orocrm_channel` to `channels`.

###OroMarketingListBundle
- Class `Oro\Bundle\MarketingListBundle\Model\ContactInformationFieldHelper`:
    - method `getQueryContactInformationColumns` was removed. Use method `getQueryContactInformationFields` instead.
    - method `getEntityContactInformationColumns` `was removed. Use method getEntityContactInformationFields` instead.
    - method `getEntityContactInformationColumnsInfo` was removed. Use method `getEntityContactInformationFieldsInfo` instead.
    - method `getEntityLevelContactInfoColumns` was removed. Use method `getEntityLevelContactInfoFields` instead.

####OroAnalyticsBundle:
- The class `Oro\Bundle\AnalyticsBundle\Model\StateManager` and its service `orocrm_analytics.model.state_manager` were removed.
- The method `scheduleRecalculation` of `Oro\Bundle\AnalyticsBundle\Model\RFMMetricStateManager` was removed. Use appropriate method from `Oro\Bundle\AnalyticsBundle\Service\CalculateAnalyticsScheduler` service.


####CalendarCRMBridgeBundle:
- CalendarCRMBridgeBundle was added to integrate OroCalendarBundle into CRM
