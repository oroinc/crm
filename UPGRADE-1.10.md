UPGRADE FROM 1.9 to 1.10
=======================

####OroCRMSalesBundle:
-- The 'status' field in OroCRM\Bundle\SalesBundle\Entity\Opportunity is changed to enum type
- Constructor for `OroCRM\Bundle\SalesBundle\Provider\ForecastOfOpportunities` was changed. New argument: `DateHelper $dateHelper`

####OroCRMTaskBundle:
- OroCRMTaskBundle moved to a separate package
- OroCRMTaskBridgeBundle was added to integrate OroCRMTaskBundle into CRM

#### OroCRMChannelBundle:
- Constructor for `OroCRM\Bundle\ChannelBundle\Provider\Lifetime\AverageLifetimeWidgetProvider` was changed. New argument: `DateFilterProcessor $filterProcessor`
