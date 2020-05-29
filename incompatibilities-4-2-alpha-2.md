MarketingCRM
------------
* The following classes were removed:
   - `LoadCampaignPerformanceReport`<sup>[[?]](https://github.com/oroinc/crm/tree/4.2.0-alpha.1/src/Oro/Bridge/MarketingCRM/Migrations/Data/ORM/LoadCampaignPerformanceReport.php#L14 "Oro\Bridge\MarketingCRM\Migrations\Migrations\Data\ORM\LoadCampaignPerformanceReport")</sup>
   - `LoadTrackingRolesData`<sup>[[?]](https://github.com/oroinc/crm/tree/4.2.0-alpha.1/src/Oro/Bridge/MarketingCRM/Migrations/Data/ORM/LoadTrackingRolesData.php#L16 "Oro\Bridge\MarketingCRM\Migrations\Migrations\Data\ORM\LoadTrackingRolesData")</sup>

SalesBundle
-----------
* The `OpportunityRepository::getForecastQB(CurrencyQueryBuilderTransformerInterface $qbTransformer, $alias, array $excludedStatuses = [ ... ])`<sup>[[?]](https://github.com/oroinc/crm/tree/4.2.0-alpha.1/src/Oro/Bundle/SalesBundle/Entity/Repository/OpportunityRepository.php#L212 "Oro\Bundle\SalesBundle\Entity\Repository\OpportunityRepository")</sup> method was changed to `OpportunityRepository::getForecastQB(CurrencyQueryBuilderTransformerInterface $qbTransformer, $alias, array $excludedStatuses = [ ... ])`<sup>[[?]](https://github.com/oroinc/crm/tree/4.1.0-alpha.2/src/Oro/Bundle/SalesBundle/Entity/Repository/OpportunityRepository.php#L213 "Oro\Bundle\SalesBundle\Entity\Repository\OpportunityRepository")</sup>

