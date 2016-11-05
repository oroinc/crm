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

####OroCaseBundle:
- `OroCRM/Bundle/CaseBundle/Entity/CaseMailboxProcessSettings` extends `Oro\Bundle\CaseBundle\Model\ExtendCaseMailboxProcessSettings`

####OroContactUsBundle:
- Removed fields `workflowItem` and `workflowStep` from entity `Oro\Bundle\ContactUsBundle\Entity\ContactRequest`

####OroMagentoBundle:
- Removed fields `workflowItem` and `workflowStep` from entity `Oro\Bundle\MagentoBundle\Entity\Cart`
- Removed fields `workflowItem` and `workflowStep` from entity `Oro\Bundle\MagentoBundle\Entity\Order`

###OroChannelBundle:
- Channel configuration file now loads from `Resources/config/oro/channels.yml` instead of `Resources/config/channel_configuration.yml`.
- Root node for channel config in `Resources/config/oro/channels.yml` were changed from `orocrm_channel` to `channels`.

###OroMarketingListBundle
- Class `Oro\Bundle\MarketingListBundle\Model\ContactInformationFieldHelper
    - method `getQueryContactInformationColumns` was removed. Use method `getQueryContactInformationFields` instead.
    - method `getEntityContactInformationColumns` `was removed. Use method getEntityContactInformationFields` instead.
    - method `getEntityContactInformationColumnsInfo` was removed. Use method `getEntityContactInformationFieldsInfo` instead.
    - method `getEntityLevelContactInfoColumns` was removed. Use method `getEntityLevelContactInfoFields` instead.


####CalendarCRMBridgeBundle:
- CalendarCRMBridgeBundle was added to integrate OroCalendarBundle into CRM
