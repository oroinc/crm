UPGRADE FROM 1.9 to 1.10
=======================

####OroCRMSalesBundle:
- The 'status' field in `OroCRM\Bundle\SalesBundle\Entity\Opportunity` is changed to enum type
- Constructor for `OroCRM\Bundle\SalesBundle\Provider\ForecastOfOpportunities` changed. New arguments: `DateHelper $dateHelper`, `OwnerHelper $ownerHelper`
- Class `OroCRM\Bundle\SalesBundle\Provider\OpportunityByStatusProvider` moved to `OroCRM\Bundle\SalesBundle\Dashboard\Provider\OpportunityByStatusProvider`. New argument: `OwnerHelper $ownerHelper`
- Deprecated method `\OroCRM\Bundle\SalesBundle\Entity\Repository\LeadRepository::getOpportunitiesByLeadSource`
  in favor of `\OroCRM\Bundle\SalesBundle\Entity\Repository\OpportunityRepository::getOpportunitiesCountGroupByLeadSource`

####OroCRMTaskBundle:
- OroCRMTaskBundle moved to a separate package
- OroCRMTaskBridgeBundle was added to integrate OroCRMTaskBundle into CRM

#### OroCRMChannelBundle:
- Constructor for `OroCRM\Bundle\ChannelBundle\Provider\Lifetime\AverageLifetimeWidgetProvider` was changed. New argument: `DateFilterProcessor $filterProcessor`
