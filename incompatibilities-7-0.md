- [ActivityContactBundle](#activitycontactbundle)
- [AnalyticsBundle](#analyticsbundle)
- [CRMBundle](#crmbundle)
- [ChannelBundle](#channelbundle)
- [ContactBundle](#contactbundle)
- [ContactUsBundle](#contactusbundle)
- [SalesBundle](#salesbundle)

ActivityContactBundle
---------------------
* The `ActivityContactRecalculateCommand::configure`<sup>[[?]](https://github.com/oroinc/crm/tree/6.1.0/src/Oro/Bundle/ActivityContactBundle/Command/ActivityContactRecalculateCommand.php#L67 "Oro\Bundle\ActivityContactBundle\Command\ActivityContactRecalculateCommand::configure")</sup> method was removed.
* The `ActivityContactRecalculateCommand::$defaultName`<sup>[[?]](https://github.com/oroinc/crm/tree/6.1.0/src/Oro/Bundle/ActivityContactBundle/Command/ActivityContactRecalculateCommand.php#L37 "Oro\Bundle\ActivityContactBundle\Command\ActivityContactRecalculateCommand::$defaultName")</sup> property was removed.

AnalyticsBundle
---------------
* The `CalculateAnalyticsCommand::$defaultName`<sup>[[?]](https://github.com/oroinc/crm/tree/6.1.0/src/Oro/Bundle/AnalyticsBundle/Command/CalculateAnalyticsCommand.php#L23 "Oro\Bundle\AnalyticsBundle\Command\CalculateAnalyticsCommand::$defaultName")</sup> property was removed.

CRMBundle
---------
* The `ExtendEntityCacheWarmer::warmUp($cacheDir)`<sup>[[?]](https://github.com/oroinc/crm/tree/6.1.0/src/Oro/Bundle/CRMBundle/CacheWarmer/ExtendEntityCacheWarmer.php#L49 "Oro\Bundle\CRMBundle\CacheWarmer\ExtendEntityCacheWarmer")</sup> method was changed to `ExtendEntityCacheWarmer::warmUp($cacheDir, $buildDir = null)`<sup>[[?]](https://github.com/oroinc/crm/tree/7.0.0/src/Oro/Bundle/CRMBundle/CacheWarmer/ExtendEntityCacheWarmer.php#L49 "Oro\Bundle\CRMBundle\CacheWarmer\ExtendEntityCacheWarmer")</sup>

ChannelBundle
-------------
* The `LifetimeAverageAggregateCommand::$defaultName`<sup>[[?]](https://github.com/oroinc/crm/tree/6.1.0/src/Oro/Bundle/ChannelBundle/Command/LifetimeAverageAggregateCommand.php#L21 "Oro\Bundle\ChannelBundle\Command\LifetimeAverageAggregateCommand::$defaultName")</sup> property was removed.

ContactBundle
-------------
* The `ContactExtension::$container`<sup>[[?]](https://github.com/oroinc/crm/tree/6.1.0/src/Oro/Bundle/ContactBundle/Twig/ContactExtension.php#L18 "Oro\Bundle\ContactBundle\Twig\ContactExtension::$container")</sup> property was removed.
* The `ContactNormalizer::normalize($object, $format = null, $context = [])`<sup>[[?]](https://github.com/oroinc/crm/tree/6.1.0/src/Oro/Bundle/ContactBundle/ImportExport/Serializer/Normalizer/ContactNormalizer.php#L42 "Oro\Bundle\ContactBundle\ImportExport\Serializer\Normalizer\ContactNormalizer")</sup> method was changed to `ContactNormalizer::normalize($object, $format = null, $context = [])`<sup>[[?]](https://github.com/oroinc/crm/tree/7.0.0/src/Oro/Bundle/ContactBundle/ImportExport/Serializer/Normalizer/ContactNormalizer.php#L42 "Oro\Bundle\ContactBundle\ImportExport\Serializer\Normalizer\ContactNormalizer")</sup>

ContactUsBundle
---------------
* The `ContactRequest::setCustomerName($customerName)`<sup>[[?]](https://github.com/oroinc/crm/tree/6.1.0/src/Oro/Bundle/ContactUsBundle/Entity/ContactRequest.php#L76 "Oro\Bundle\ContactUsBundle\Entity\ContactRequest")</sup> method was changed to `ContactRequest::setCustomerName($customerName = null)`<sup>[[?]](https://github.com/oroinc/crm/tree/7.0.0/src/Oro/Bundle/ContactUsBundle/Entity/ContactRequest.php#L74 "Oro\Bundle\ContactUsBundle\Entity\ContactRequest")</sup>

SalesBundle
-----------
* The `RecalculateLifetimeCommand::$defaultName`<sup>[[?]](https://github.com/oroinc/crm/tree/6.1.0/src/Oro/Bundle/SalesBundle/Command/RecalculateLifetimeCommand.php#L21 "Oro\Bundle\SalesBundle\Command\RecalculateLifetimeCommand::$defaultName")</sup> property was removed.
* The `RemoveSalesFunnelEntityConfigWarmer::warmUp($cacheDir)`<sup>[[?]](https://github.com/oroinc/crm/tree/6.1.0/src/Oro/Bundle/SalesBundle/CacheWarmer/RemoveSalesFunnelEntityConfigWarmer.php#L39 "Oro\Bundle\SalesBundle\CacheWarmer\RemoveSalesFunnelEntityConfigWarmer")</sup> method was changed to `RemoveSalesFunnelEntityConfigWarmer::warmUp($cacheDir, $buildDir = null)`<sup>[[?]](https://github.com/oroinc/crm/tree/7.0.0/src/Oro/Bundle/SalesBundle/CacheWarmer/RemoveSalesFunnelEntityConfigWarmer.php#L39 "Oro\Bundle\SalesBundle\CacheWarmer\RemoveSalesFunnelEntityConfigWarmer")</sup>

