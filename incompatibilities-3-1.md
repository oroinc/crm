- [ActivityContactBundle](#activitycontactbundle)
- [ContactBundle](#contactbundle)
- [SalesBundle](#salesbundle)

ActivityContactBundle
---------------------
* The `UpdateActivityContactFields::__construct(DoctrineHelper $doctrineHelper, ConfigManager $configManager, ActivityContactProvider $activityContactProvider, $excludedActions)`<sup>[[?]](https://github.com/oroinc/crm/tree/3.1.0-rc/src/Oro/Bundle/ActivityContactBundle/Api/Processor/Config/UpdateActivityContactFields.php#L40 "Oro\Bundle\ActivityContactBundle\Api\Processor\Config\UpdateActivityContactFields")</sup> method was changed to `UpdateActivityContactFields::__construct(DoctrineHelper $doctrineHelper, ConfigManager $configManager, ActivityContactProvider $activityContactProvider, array $excludedActions)`<sup>[[?]](https://github.com/oroinc/crm/tree/3.1.0/src/Oro/Bundle/ActivityContactBundle/Api/Processor/Config/UpdateActivityContactFields.php#L40 "Oro\Bundle\ActivityContactBundle\Api\Processor\Config\UpdateActivityContactFields")</sup>

ContactBundle
-------------
* The `ContactPostImportProcessor::__construct(ContactEmailAddressHandler $contactEmailAddressHandler, DatabaseExceptionHelper $databaseExceptionHelper, JobStorage $jobStorage, LoggerInterface $logger)`<sup>[[?]](https://github.com/oroinc/crm/tree/3.1.0-rc/src/Oro/Bundle/ContactBundle/Async/ContactPostImportProcessor.php#L45 "Oro\Bundle\ContactBundle\Async\ContactPostImportProcessor")</sup> method was changed to `ContactPostImportProcessor::__construct(ContactEmailAddressHandler $contactEmailAddressHandler, JobStorage $jobStorage, LoggerInterface $logger)`<sup>[[?]](https://github.com/oroinc/crm/tree/3.1.0/src/Oro/Bundle/ContactBundle/Async/ContactPostImportProcessor.php#L39 "Oro\Bundle\ContactBundle\Async\ContactPostImportProcessor")</sup>

SalesBundle
-----------
* The `ConfigProvider::__construct(ConfigManager $configManager)`<sup>[[?]](https://github.com/oroinc/crm/tree/3.1.0-rc/src/Oro/Bundle/SalesBundle/Provider/Customer/ConfigProvider.php#L20 "Oro\Bundle\SalesBundle\Provider\Customer\ConfigProvider")</sup> method was changed to `ConfigProvider::__construct(ConfigManager $configManager, ConfigCache $cache)`<sup>[[?]](https://github.com/oroinc/crm/tree/3.1.0/src/Oro/Bundle/SalesBundle/Provider/Customer/ConfigProvider.php#L24 "Oro\Bundle\SalesBundle\Provider\Customer\ConfigProvider")</sup>

