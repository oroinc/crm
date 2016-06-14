UPGRADE FROM 1.9 to 1.10
=======================

####OroCRMSalesBundle:
- The 'status' field in `OroCRM\Bundle\SalesBundle\Entity\Opportunity` is changed to enum type

#### OroCRMCallBundle:
- The 'duration' field in `OroCRM\Bundle\CallBundle\Entity\Call` is changed from `DateTime` 
to `duration` type which accepts a time duration in seconds

####OroCRMTaskBundle:
- OroCRMTaskBundle moved to a separate package
- OroCRMTaskBridgeBundle was added to integrate OroCRMTaskBundle into CRM
