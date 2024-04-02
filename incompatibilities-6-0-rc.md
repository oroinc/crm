- [ActivityContactBundle](#activitycontactbundle)
- [AnalyticsBundle](#analyticsbundle)
- [CRMBundle](#crmbundle)
- [CalendarCRM](#calendarcrm)
- [CallCRM](#callcrm)
- [ChannelBundle](#channelbundle)
- [ContactBundle](#contactbundle)
- [ContactUsBundle](#contactusbundle)
- [SalesBundle](#salesbundle)
- [TaskCRM](#taskcrm)

ActivityContactBundle
---------------------
* The `ActivityContactProvider::getActivityDirection($activity, $target)`<sup>[[?]](https://github.com/oroinc/crm/tree/5.1.0/src/Oro/Bundle/ActivityContactBundle/Provider/ActivityContactProvider.php#L39 "Oro\Bundle\ActivityContactBundle\Provider\ActivityContactProvider")</sup> method was changed to `ActivityContactProvider::getActivityDirection($activity, $target)`<sup>[[?]](https://github.com/oroinc/crm/tree/6.0.0-rc/src/Oro/Bundle/ActivityContactBundle/Provider/ActivityContactProvider.php#L34 "Oro\Bundle\ActivityContactBundle\Provider\ActivityContactProvider")</sup>

AnalyticsBundle
---------------
* The `ChannelTypeExtension::__construct(DoctrineHelper $doctrineHelper, $interface, $rfmCategoryClass)`<sup>[[?]](https://github.com/oroinc/crm/tree/5.1.0/src/Oro/Bundle/AnalyticsBundle/Form/Extension/ChannelTypeExtension.php#L40 "Oro\Bundle\AnalyticsBundle\Form\Extension\ChannelTypeExtension")</sup> method was changed to `ChannelTypeExtension::__construct(DoctrineHelper $doctrineHelper, $interface, $rfmCategoryClass)`<sup>[[?]](https://github.com/oroinc/crm/tree/6.0.0-rc/src/Oro/Bundle/AnalyticsBundle/Form/Extension/ChannelTypeExtension.php#L33 "Oro\Bundle\AnalyticsBundle\Form\Extension\ChannelTypeExtension")</sup>
* The following properties in class `ChannelTypeExtension`<sup>[[?]](https://github.com/oroinc/crm/tree/5.1.0/src/Oro/Bundle/AnalyticsBundle/Form/Extension/ChannelTypeExtension.php#L28 "Oro\Bundle\AnalyticsBundle\Form\Extension\ChannelTypeExtension")</sup> were removed:
   - `$doctrineHelper`<sup>[[?]](https://github.com/oroinc/crm/tree/5.1.0/src/Oro/Bundle/AnalyticsBundle/Form/Extension/ChannelTypeExtension.php#L28 "Oro\Bundle\AnalyticsBundle\Form\Extension\ChannelTypeExtension::$doctrineHelper")</sup>
   - `$interface`<sup>[[?]](https://github.com/oroinc/crm/tree/5.1.0/src/Oro/Bundle/AnalyticsBundle/Form/Extension/ChannelTypeExtension.php#L33 "Oro\Bundle\AnalyticsBundle\Form\Extension\ChannelTypeExtension::$interface")</sup>

CRMBundle
---------
* The `ExtendEntityCacheWarmer::warmUp($cacheDir)`<sup>[[?]](https://github.com/oroinc/crm/tree/5.1.0/src/Oro/Bundle/CRMBundle/CacheWarmer/ExtendEntityCacheWarmer.php#L47 "Oro\Bundle\CRMBundle\CacheWarmer\ExtendEntityCacheWarmer")</sup> method was changed to `ExtendEntityCacheWarmer::warmUp($cacheDir)`<sup>[[?]](https://github.com/oroinc/crm/tree/6.0.0-rc/src/Oro/Bundle/CRMBundle/CacheWarmer/ExtendEntityCacheWarmer.php#L48 "Oro\Bundle\CRMBundle\CacheWarmer\ExtendEntityCacheWarmer")</sup>

CalendarCRM
-----------
* The `OroCalendarCRMBridgeBundleInstaller::setActivityExtension`<sup>[[?]](https://github.com/oroinc/crm/tree/5.1.0/src/Oro/Bridge/CalendarCRM/Migrations/Schema/OroCalendarCRMBridgeBundleInstaller.php#L21 "Oro\Bridge\CalendarCRM\Migrations\Schema\OroCalendarCRMBridgeBundleInstaller::setActivityExtension")</sup> method was removed.
* The following methods in class `LoadUsersCalendarData`<sup>[[?]](https://github.com/oroinc/crm/tree/5.1.0/src/Oro/Bridge/CalendarCRM/Migrations/Data/Demo/ORM/LoadUsersCalendarData.php#L67 "Oro\Bridge\CalendarCRM\Migrations\Data\Demo\ORM\LoadUsersCalendarData")</sup> were removed:
   - `setContainer`<sup>[[?]](https://github.com/oroinc/crm/tree/5.1.0/src/Oro/Bridge/CalendarCRM/Migrations/Data/Demo/ORM/LoadUsersCalendarData.php#L67 "Oro\Bridge\CalendarCRM\Migrations\Data\Demo\ORM\LoadUsersCalendarData::setContainer")</sup>
   - `setSecurityContext`<sup>[[?]](https://github.com/oroinc/crm/tree/5.1.0/src/Oro/Bridge/CalendarCRM/Migrations/Data/Demo/ORM/LoadUsersCalendarData.php#L330 "Oro\Bridge\CalendarCRM\Migrations\Data\Demo\ORM\LoadUsersCalendarData::setSecurityContext")</sup>
* The following properties in class `LoadUsersCalendarData`<sup>[[?]](https://github.com/oroinc/crm/tree/5.1.0/src/Oro/Bridge/CalendarCRM/Migrations/Data/Demo/ORM/LoadUsersCalendarData.php#L36 "Oro\Bridge\CalendarCRM\Migrations\Data\Demo\ORM\LoadUsersCalendarData")</sup> were removed:
   - `$user`<sup>[[?]](https://github.com/oroinc/crm/tree/5.1.0/src/Oro/Bridge/CalendarCRM/Migrations/Data/Demo/ORM/LoadUsersCalendarData.php#L36 "Oro\Bridge\CalendarCRM\Migrations\Data\Demo\ORM\LoadUsersCalendarData::$user")</sup>
   - `$calendar`<sup>[[?]](https://github.com/oroinc/crm/tree/5.1.0/src/Oro/Bridge/CalendarCRM/Migrations/Data/Demo/ORM/LoadUsersCalendarData.php#L39 "Oro\Bridge\CalendarCRM\Migrations\Data\Demo\ORM\LoadUsersCalendarData::$calendar")</sup>
   - `$organization`<sup>[[?]](https://github.com/oroinc/crm/tree/5.1.0/src/Oro/Bridge/CalendarCRM/Migrations/Data/Demo/ORM/LoadUsersCalendarData.php#L42 "Oro\Bridge\CalendarCRM\Migrations\Data\Demo\ORM\LoadUsersCalendarData::$organization")</sup>
   - `$em`<sup>[[?]](https://github.com/oroinc/crm/tree/5.1.0/src/Oro/Bridge/CalendarCRM/Migrations/Data/Demo/ORM/LoadUsersCalendarData.php#L45 "Oro\Bridge\CalendarCRM\Migrations\Data\Demo\ORM\LoadUsersCalendarData::$em")</sup>
   - `$tokenStorage`<sup>[[?]](https://github.com/oroinc/crm/tree/5.1.0/src/Oro/Bridge/CalendarCRM/Migrations/Data/Demo/ORM/LoadUsersCalendarData.php#L48 "Oro\Bridge\CalendarCRM\Migrations\Data\Demo\ORM\LoadUsersCalendarData::$tokenStorage")</sup>

CallCRM
-------
* The `OroCallCRMBridgeBundleInstaller::setActivityExtension`<sup>[[?]](https://github.com/oroinc/crm/tree/5.1.0/src/Oro/Bridge/CallCRM/Migrations/Schema/OroCallCRMBridgeBundleInstaller.php#L21 "Oro\Bridge\CallCRM\Migrations\Schema\OroCallCRMBridgeBundleInstaller::setActivityExtension")</sup> method was removed.
* The following methods in class `LoadCallData`<sup>[[?]](https://github.com/oroinc/crm/tree/5.1.0/src/Oro/Bridge/CallCRM/Migrations/Data/Demo/ORM/LoadCallData.php#L49 "Oro\Bridge\CallCRM\Migrations\Data\Demo\ORM\LoadCallData")</sup> were removed:
   - `setContainer`<sup>[[?]](https://github.com/oroinc/crm/tree/5.1.0/src/Oro/Bridge/CallCRM/Migrations/Data/Demo/ORM/LoadCallData.php#L49 "Oro\Bridge\CallCRM\Migrations\Data\Demo\ORM\LoadCallData::setContainer")</sup>
   - `randomDate`<sup>[[?]](https://github.com/oroinc/crm/tree/5.1.0/src/Oro/Bridge/CallCRM/Migrations/Data/Demo/ORM/LoadCallData.php#L152 "Oro\Bridge\CallCRM\Migrations\Data\Demo\ORM\LoadCallData::randomDate")</sup>
   - `setSecurityContext`<sup>[[?]](https://github.com/oroinc/crm/tree/5.1.0/src/Oro/Bridge/CallCRM/Migrations/Data/Demo/ORM/LoadCallData.php#L168 "Oro\Bridge\CallCRM\Migrations\Data\Demo\ORM\LoadCallData::setSecurityContext")</sup>
* The `OroCallCRMBridgeBundleInstaller::$activityExtension`<sup>[[?]](https://github.com/oroinc/crm/tree/5.1.0/src/Oro/Bridge/CallCRM/Migrations/Schema/OroCallCRMBridgeBundleInstaller.php#L16 "Oro\Bridge\CallCRM\Migrations\Schema\OroCallCRMBridgeBundleInstaller::$activityExtension")</sup> property was removed.
* The following properties in class `LoadCallData`<sup>[[?]](https://github.com/oroinc/crm/tree/5.1.0/src/Oro/Bridge/CallCRM/Migrations/Data/Demo/ORM/LoadCallData.php#L33 "Oro\Bridge\CallCRM\Migrations\Data\Demo\ORM\LoadCallData")</sup> were removed:
   - `$organization`<sup>[[?]](https://github.com/oroinc/crm/tree/5.1.0/src/Oro/Bridge/CallCRM/Migrations/Data/Demo/ORM/LoadCallData.php#L33 "Oro\Bridge\CallCRM\Migrations\Data\Demo\ORM\LoadCallData::$organization")</sup>
   - `$container`<sup>[[?]](https://github.com/oroinc/crm/tree/5.1.0/src/Oro/Bridge/CallCRM/Migrations/Data/Demo/ORM/LoadCallData.php#L36 "Oro\Bridge\CallCRM\Migrations\Data\Demo\ORM\LoadCallData::$container")</sup>

ChannelBundle
-------------
* The `LifetimeHistoryStatusUpdateManager::massUpdate($records, $status)`<sup>[[?]](https://github.com/oroinc/crm/tree/5.1.0/src/Oro/Bundle/ChannelBundle/Entity/Manager/LifetimeHistoryStatusUpdateManager.php#L33 "Oro\Bundle\ChannelBundle\Entity\Manager\LifetimeHistoryStatusUpdateManager")</sup> method was changed to `LifetimeHistoryStatusUpdateManager::massUpdate($records, $status)`<sup>[[?]](https://github.com/oroinc/crm/tree/6.0.0-rc/src/Oro/Bundle/ChannelBundle/Entity/Manager/LifetimeHistoryStatusUpdateManager.php#L33 "Oro\Bundle\ChannelBundle\Entity\Manager\LifetimeHistoryStatusUpdateManager")</sup>
* The `LifetimeHistoryStatusUpdateTopic::createMessage($records, $status)`<sup>[[?]](https://github.com/oroinc/crm/tree/5.1.0/src/Oro/Bundle/ChannelBundle/Async/Topic/LifetimeHistoryStatusUpdateTopic.php#L40 "Oro\Bundle\ChannelBundle\Async\Topic\LifetimeHistoryStatusUpdateTopic")</sup> method was changed to `LifetimeHistoryStatusUpdateTopic::createMessage($records, $status)`<sup>[[?]](https://github.com/oroinc/crm/tree/6.0.0-rc/src/Oro/Bundle/ChannelBundle/Async/Topic/LifetimeHistoryStatusUpdateTopic.php#L40 "Oro\Bundle\ChannelBundle\Async\Topic\LifetimeHistoryStatusUpdateTopic")</sup>
* The `ChannelController::getMessageProducer`<sup>[[?]](https://github.com/oroinc/crm/tree/5.1.0/src/Oro/Bundle/ChannelBundle/Controller/ChannelController.php#L125 "Oro\Bundle\ChannelBundle\Controller\ChannelController::getMessageProducer")</sup> method was removed.
* The `ChannelLimitationHandler::$channelPropertyName`<sup>[[?]](https://github.com/oroinc/crm/tree/5.1.0/src/Oro/Bundle/ChannelBundle/Autocomplete/ChannelLimitationHandler.php#L16 "Oro\Bundle\ChannelBundle\Autocomplete\ChannelLimitationHandler::$channelPropertyName")</sup> property was removed.

ContactBundle
-------------
* The `ContactNameFormatter`<sup>[[?]](https://github.com/oroinc/crm/tree/5.1.0/src/Oro/Bundle/ContactBundle/Formatter/ContactNameFormatter.php#L8 "Oro\Bundle\ContactBundle\Formatter\ContactNameFormatter")</sup> class was removed.
* The `ContactEntityNameProvider::$contactCollectionsMap`<sup>[[?]](https://github.com/oroinc/crm/tree/5.1.0/src/Oro/Bundle/ContactBundle/Provider/ContactEntityNameProvider.php#L13 "Oro\Bundle\ContactBundle\Provider\ContactEntityNameProvider::$contactCollectionsMap")</sup> property was removed.

ContactUsBundle
---------------
* The following methods in class `ContactRequest`<sup>[[?]](https://github.com/oroinc/crm/tree/5.1.0/src/Oro/Bundle/ContactUsBundle/Entity/ContactRequest.php#L114 "Oro\Bundle\ContactUsBundle\Entity\ContactRequest")</sup> were removed:
   - `setOrganizationName`<sup>[[?]](https://github.com/oroinc/crm/tree/5.1.0/src/Oro/Bundle/ContactUsBundle/Entity/ContactRequest.php#L114 "Oro\Bundle\ContactUsBundle\Entity\ContactRequest::setOrganizationName")</sup>
   - `getOrganizationName`<sup>[[?]](https://github.com/oroinc/crm/tree/5.1.0/src/Oro/Bundle/ContactUsBundle/Entity/ContactRequest.php#L122 "Oro\Bundle\ContactUsBundle\Entity\ContactRequest::getOrganizationName")</sup>
* The `ContactRequest::setPreferredContactMethod($preferredContactMethod)`<sup>[[?]](https://github.com/oroinc/crm/tree/5.1.0/src/Oro/Bundle/ContactUsBundle/Entity/ContactRequest.php#L130 "Oro\Bundle\ContactUsBundle\Entity\ContactRequest")</sup> method was changed to `ContactRequest::setPreferredContactMethod($preferredContactMethod)`<sup>[[?]](https://github.com/oroinc/crm/tree/6.0.0-rc/src/Oro/Bundle/ContactUsBundle/Entity/ContactRequest.php#L86 "Oro\Bundle\ContactUsBundle\Entity\ContactRequest")</sup>
* The `ContactRequest::$organizationName`<sup>[[?]](https://github.com/oroinc/crm/tree/5.1.0/src/Oro/Bundle/ContactUsBundle/Entity/ContactRequest.php#L63 "Oro\Bundle\ContactUsBundle\Entity\ContactRequest::$organizationName")</sup> property was removed.
* The `ContactRequest::$customerName`<sup>[[?]](https://github.com/oroinc/crm/blob/6.0.0/src/Oro/Bundle/ContactUsBundle/Entity/ContactRequest.php#L52 "Oro\Bundle\ContactUsBundle\Entity\ContactRequest::$customerName")</sup> property was added.

SalesBundle
-----------
* The `CustomerFactory`<sup>[[?]](https://github.com/oroinc/crm/tree/5.1.0/src/Oro/Bundle/SalesBundle/Entity/Factory/CustomerFactory.php#L10 "Oro\Bundle\SalesBundle\Entity\Factory\CustomerFactory")</sup> class was removed.
* The following methods in class `CustomerExtension`<sup>[[?]](https://github.com/oroinc/crm/tree/5.1.0/src/Oro/Bundle/SalesBundle/Migration/Extension/CustomerExtension.php#L29 "Oro\Bundle\SalesBundle\Migration\Extension\CustomerExtension")</sup> were removed:
   - `setExtendExtension`<sup>[[?]](https://github.com/oroinc/crm/tree/5.1.0/src/Oro/Bundle/SalesBundle/Migration/Extension/CustomerExtension.php#L29 "Oro\Bundle\SalesBundle\Migration\Extension\CustomerExtension::setExtendExtension")</sup>
   - `setNameGenerator`<sup>[[?]](https://github.com/oroinc/crm/tree/5.1.0/src/Oro/Bundle/SalesBundle/Migration/Extension/CustomerExtension.php#L37 "Oro\Bundle\SalesBundle\Migration\Extension\CustomerExtension::setNameGenerator")</sup>
* The following properties in class `CustomerExtension`<sup>[[?]](https://github.com/oroinc/crm/tree/5.1.0/src/Oro/Bundle/SalesBundle/Migration/Extension/CustomerExtension.php#L21 "Oro\Bundle\SalesBundle\Migration\Extension\CustomerExtension")</sup> were removed:
   - `$extendExtension`<sup>[[?]](https://github.com/oroinc/crm/tree/5.1.0/src/Oro/Bundle/SalesBundle/Migration/Extension/CustomerExtension.php#L21 "Oro\Bundle\SalesBundle\Migration\Extension\CustomerExtension::$extendExtension")</sup>
   - `$nameGenerator`<sup>[[?]](https://github.com/oroinc/crm/tree/5.1.0/src/Oro/Bundle/SalesBundle/Migration/Extension/CustomerExtension.php#L24 "Oro\Bundle\SalesBundle\Migration\Extension\CustomerExtension::$nameGenerator")</sup>
* The `CustomerToStringTransformer::__construct(DataTransformerInterface $entityToStringTransformer, AccountCustomerManager $accountCustomerManager, CustomerFactory $customerFactory)`<sup>[[?]](https://github.com/oroinc/crm/tree/5.1.0/src/Oro/Bundle/SalesBundle/Form/DataTransformer/CustomerToStringTransformer.php#L17 "Oro\Bundle\SalesBundle\Form\DataTransformer\CustomerToStringTransformer")</sup> method was changed to `CustomerToStringTransformer::__construct(DataTransformerInterface $entityToStringTransformer, AccountCustomerManager $accountCustomerManager)`<sup>[[?]](https://github.com/oroinc/crm/tree/6.0.0-rc/src/Oro/Bundle/SalesBundle/Form/DataTransformer/CustomerToStringTransformer.php#L16 "Oro\Bundle\SalesBundle\Form\DataTransformer\CustomerToStringTransformer")</sup>
* The `OpportunityRepository::getForecastQB(CurrencyQueryBuilderTransformerInterface $qbTransformer, $alias, $excludedStatuses = [ ... ])`<sup>[[?]](https://github.com/oroinc/crm/tree/5.1.0/src/Oro/Bundle/SalesBundle/Entity/Repository/OpportunityRepository.php#L203 "Oro\Bundle\SalesBundle\Entity\Repository\OpportunityRepository")</sup> method was changed to `OpportunityRepository::getForecastQB(CurrencyQueryBuilderTransformerInterface $qbTransformer, $alias, $excludedStatuses = [ ... ])`<sup>[[?]](https://github.com/oroinc/crm/tree/6.0.0-rc/src/Oro/Bundle/SalesBundle/Entity/Repository/OpportunityRepository.php#L203 "Oro\Bundle\SalesBundle\Entity\Repository\OpportunityRepository")</sup>
* The `AccountCustomerManager::__construct(DoctrineHelper $doctrineHelper, ConfigProvider $provider, AccountProviderInterface $accountProvider, CustomerFactory $customerFactory)`<sup>[[?]](https://github.com/oroinc/crm/tree/5.1.0/src/Oro/Bundle/SalesBundle/Entity/Manager/AccountCustomerManager.php#L23 "Oro\Bundle\SalesBundle\Entity\Manager\AccountCustomerManager")</sup> method was changed to `AccountCustomerManager::__construct(DoctrineHelper $doctrineHelper, ConfigProvider $provider, AccountProviderInterface $accountProvider)`<sup>[[?]](https://github.com/oroinc/crm/tree/6.0.0-rc/src/Oro/Bundle/SalesBundle/Entity/Manager/AccountCustomerManager.php#L22 "Oro\Bundle\SalesBundle\Entity\Manager\AccountCustomerManager")</sup>
* The `RemoveSalesFunnelEntityConfigWarmer::warmUp($cacheDir)`<sup>[[?]](https://github.com/oroinc/crm/tree/5.1.0/src/Oro/Bundle/SalesBundle/CacheWarmer/RemoveSalesFunnelEntityConfigWarmer.php#L44 "Oro\Bundle\SalesBundle\CacheWarmer\RemoveSalesFunnelEntityConfigWarmer")</sup> method was changed to `RemoveSalesFunnelEntityConfigWarmer::warmUp($cacheDir)`<sup>[[?]](https://github.com/oroinc/crm/tree/6.0.0-rc/src/Oro/Bundle/SalesBundle/CacheWarmer/RemoveSalesFunnelEntityConfigWarmer.php#L44 "Oro\Bundle\SalesBundle\CacheWarmer\RemoveSalesFunnelEntityConfigWarmer")</sup>

TaskCRM
-------
* The `OroTaskCRMBundleInstaller::setActivityExtension`<sup>[[?]](https://github.com/oroinc/crm/tree/5.1.0/src/Oro/Bridge/TaskCRM/Migrations/Schema/OroTaskCRMBundleInstaller.php#L21 "Oro\Bridge\TaskCRM\Migrations\Schema\OroTaskCRMBundleInstaller::setActivityExtension")</sup> method was removed.
* The `LoadTaskData::setContainer`<sup>[[?]](https://github.com/oroinc/crm/tree/5.1.0/src/Oro/Bridge/TaskCRM/Migrations/Data/Demo/ORM/LoadTaskData.php#L95 "Oro\Bridge\TaskCRM\Migrations\Data\Demo\ORM\LoadTaskData::setContainer")</sup> method was removed.
* The `OroTaskCRMBundleInstaller::$activityExtension`<sup>[[?]](https://github.com/oroinc/crm/tree/5.1.0/src/Oro/Bridge/TaskCRM/Migrations/Schema/OroTaskCRMBundleInstaller.php#L16 "Oro\Bridge\TaskCRM\Migrations\Schema\OroTaskCRMBundleInstaller::$activityExtension")</sup> property was removed.
* The `LoadTaskData::$container`<sup>[[?]](https://github.com/oroinc/crm/tree/5.1.0/src/Oro/Bridge/TaskCRM/Migrations/Data/Demo/ORM/LoadTaskData.php#L78 "Oro\Bridge\TaskCRM\Migrations\Data\Demo\ORM\LoadTaskData::$container")</sup> property was removed.

