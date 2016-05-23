UPGRADE FROM 1.10 to 2.0 

####OroCRMSalesBundle:
- Removed fields `workflowItem` and `workflowStep` from entity `OroCRM\Bundle\SalesBundle\Entity\Lead`
- Removed fields `workflowItem` and `workflowStep` from entity `OroCRM\Bundle\SalesBundle\Entity\Opportunity`
- Removed fields `workflowItem` and `workflowStep` from entity `OroCRM\Bundle\SalesBundle\Entity\SalesFunnel`

####OroCRMContactUsBundle:
- Removed fields `workflowItem` and `workflowStep` from entity `OroCRM\Bundle\ContactUsBundle\Entity\ContactRequest`

####OroCRMMagentoBundle:
- Removed fields `workflowItem` and `workflowStep` from entity `OroCRM\Bundle\MagentoBundle\Entity\Cart`
- Removed fields `workflowItem` and `workflowStep` from entity `OroCRM\Bundle\MagentoBundle\Entity\Order`

####OroCRMChannelBundle:
- The event `orocrm_channel.channel.status_change` was removed. Use message queue topic `orocrm_channel.channel.status_change` instead.
- `ChangeIntegrationStatusListener` class was removed.
- `ChannelChangeStatusEvent` was removed.
- The parameter `orocrm_channel.event_listener.change_integration_status.class` was removed.
- The parameter `orocrm_channel.event_listener.timezone_change.class` was removed.