UPGRADE FROM 1.9 to 1.10
=======================

####OroCRMSalesBundle:
- The 'status' field in `OroCRM\Bundle\SalesBundle\Entity\Opportunity` is changed to enum type
- Constructor for `OroCRM\Bundle\SalesBundle\Provider\ForecastOfOpportunities` changed. New arguments: `DateHelper $dateHelper`, `OwnerHelper $ownerHelper`
- Class `OroCRM\Bundle\SalesBundle\Provider\OpportunityByStatusProvider` moved to `OroCRM\Bundle\SalesBundle\Dashboard\Provider\OpportunityByStatusProvider`. New argument: `OwnerHelper $ownerHelper`

#### OroCRMCallBundle:
- The `duration` field in `OroCRM\Bundle\CallBundle\Entity\Call` is changed to `duration` DB type 
from `DateTime` which accepts a (int) duration in seconds.
Updating Call `duration` field (API POST/PUT, Forms) now accepts strings with formats '*HH*:*MM*:*SS*', '*HH*h *MM*m *SS*s' or `(int)` seconds
Retrieving Call `duration` field (API GET) now returns `(int)` seconds instead of 'HH:MM:SS' formatted string

####OroCRMTaskBundle:
- OroCRMTaskBundle moved to a separate package
- OroCRMTaskBridgeBundle was added to integrate OroCRMTaskBundle into CRM

#### OroCRMChannelBundle:
- Constructor for `OroCRM\Bundle\ChannelBundle\Provider\Lifetime\AverageLifetimeWidgetProvider` was changed. New argument: `DateFilterProcessor $filterProcessor`
