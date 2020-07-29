ContactBundle
-------------
* The `ContactPostImportProcessor::__construct(ContactEmailAddressHandler $contactEmailAddressHandler, JobStorage $jobStorage, LoggerInterface $logger)`<sup>[[?]](https://github.com/oroinc/crm/tree/4.2.0-alpha.2/src/Oro/Bundle/ContactBundle/Async/ContactPostImportProcessor.php#L39 "Oro\Bundle\ContactBundle\Async\ContactPostImportProcessor")</sup> method was changed to `ContactPostImportProcessor::__construct(ContactEmailAddressHandler $contactEmailAddressHandler, DoctrineHelper $doctrineHelper, LoggerInterface $logger)`<sup>[[?]](https://github.com/oroinc/crm/tree/4.2.0-alpha.3/src/Oro/Bundle/ContactBundle/Async/ContactPostImportProcessor.php#L41 "Oro\Bundle\ContactBundle\Async\ContactPostImportProcessor")</sup>

SalesBundle
-----------
* The `OpportunityRepository::getForecastQB(CurrencyQueryBuilderTransformerInterface $qbTransformer, $alias, array $excludedStatuses = [ ... ])`<sup>[[?]](https://github.com/oroinc/crm/tree/4.2.0-alpha.2/src/Oro/Bundle/SalesBundle/Entity/Repository/OpportunityRepository.php#L213 "Oro\Bundle\SalesBundle\Entity\Repository\OpportunityRepository")</sup> method was changed to `OpportunityRepository::getForecastQB(CurrencyQueryBuilderTransformerInterface $qbTransformer, $alias, array $excludedStatuses = [ ... ])`<sup>[[?]](https://github.com/oroinc/crm/tree/4.2.0-alpha.3/src/Oro/Bundle/SalesBundle/Entity/Repository/OpportunityRepository.php#L213 "Oro\Bundle\SalesBundle\Entity\Repository\OpportunityRepository")</sup>

