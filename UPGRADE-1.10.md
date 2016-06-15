UPGRADE FROM 1.9 to 1.10
=======================

####OroCRMSalesBundle:
- The 'status' field in `OroCRM\Bundle\SalesBundle\Entity\Opportunity` is changed to enum type

#### OroCRMCallBundle:
- The `duration` field in `OroCRM\Bundle\CallBundle\Entity\Call` is changed to `duration` DB type 
from `DateTime` which accepts a (int) duration in seconds.
Updating Call `duration` field (API POST/PUT, Forms) now accepts strings with formats '*HH*:*MM*:*SS*', '*HH*h *MM*m *SS*s' or `(int)` seconds
Retrieving Call `duation` field (API GET) now returns `(int)` seconds instead of 'HH:MM:SS' formatted string

####OroCRMTaskBundle:
- OroCRMTaskBundle moved to a separate package
- OroCRMTaskBridgeBundle was added to integrate OroCRMTaskBundle into CRM
