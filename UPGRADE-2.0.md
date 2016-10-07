UPGRADE FROM 1.10 to 2.0 
========================

####OroCRMSalesBundle:
- Removed fields `workflowItem` and `workflowStep` from entity `OroCRM\Bundle\SalesBundle\Entity\Lead`
- Removed fields `workflowItem` and `workflowStep` from entity `OroCRM\Bundle\SalesBundle\Entity\Opportunity`
- Removed fields `workflowItem` and `workflowStep` from entity `OroCRM\Bundle\SalesBundle\Entity\SalesFunnel`
- `OroCRM/Bundle/SalesBundle/Entity/LeadMailboxProcessSettings` extends `OroCRM\Bundle\SalesBundle\Model\ExtendLeadMailboxProcessSettings`

####OroCRMCaseBundle:
- `OroCRM/Bundle/CaseBundle/Entity/CaseMailboxProcessSettings` extends `OroCRM\Bundle\CaseBundle\Model\ExtendCaseMailboxProcessSettings`

####OroCRMContactUsBundle:
- Removed fields `workflowItem` and `workflowStep` from entity `OroCRM\Bundle\ContactUsBundle\Entity\ContactRequest`

####OroCRMMagentoBundle:
- Removed fields `workflowItem` and `workflowStep` from entity `OroCRM\Bundle\MagentoBundle\Entity\Cart`
- Removed fields `workflowItem` and `workflowStep` from entity `OroCRM\Bundle\MagentoBundle\Entity\Order`

###OroCRNChannelBundle:
- Channel configuration file now loads from `Resources/config/oro/channels.yml` instead of `Resources/config/channel_configuration.yml`.
- Root node for channel config in `Resources/config/oro/channels.yml` were changed from `orocrm_channel` to `channels`.

####CRMCalendarBridgeBundle:
- CRMCalendarBridgeBundle was added to integrate OroCalendarBundle into CRM