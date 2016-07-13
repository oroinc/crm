UPGRADE FROM 1.9 to 1.10
=======================

####OroCRMSalesBundle:
- The 'status' field in OroCRM\Bundle\SalesBundle\Entity\Opportunity is changed to enum type
- The 'status' field in OroCRM\Bundle\SalesBundle\Entity\Lead is changed to enum type
- The OroCRM\Bundle\SalesBundle\Entity\LeadStatus is deprecated due to enum type usage
- The 'address' field in OroCRM\Bundle\SalesBundle\Entity\Lead is deprecated and will be removed in the next release. Addresses field is used instead now to store a collection of LeadAddress entities.
 If any custom fields were added to the oro_address table, they should be added to LeadAddress entity and migrated to the orocrm_lead_address table.

####OroCRMTaskBundle:
- OroCRMTaskBundle moved to a separate package
- OroCRMTaskBridgeBundle was added to integrate OroCRMTaskBundle into CRM
