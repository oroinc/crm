UPGRADE FROM 1.9 to 1.10
=======================

####OroCRMSalesBundle:
- The 'status' field in OroCRM\Bundle\SalesBundle\Entity\Opportunity is changed to enum type
- The 'status' field in OroCRM\Bundle\SalesBundle\Entity\Lead is changed to enum type
- The OroCRM\Bundle\SalesBundle\Entity\LeadStatus is deprecated due to enum type usage

####OroCRMTaskBundle:
- OroCRMTaskBundle moved to a separate package
- OroCRMTaskBridgeBundle was added to integrate OroCRMTaskBundle into CRM
