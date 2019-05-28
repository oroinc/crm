- [CaseBundle](#casebundle)
- [ChannelBundle](#channelbundle)
- [MagentoBundle](#magentobundle)
- [SalesBundle](#salesbundle)

CaseBundle
----------
* The `ViewFactory::__construct(AuthorizationCheckerInterface $authorizationChecker, RouterInterface $router, EntityNameResolver $entityNameResolver, DateTimeFormatter $dateTimeFormatter, AttachmentManager $attachmentManager)`<sup>[[?]](https://github.com/oroinc/crm/tree/4.0.0-beta/src/Oro/Bundle/CaseBundle/Model/ViewFactory.php#L42 "Oro\Bundle\CaseBundle\Model\ViewFactory")</sup> method was changed to `ViewFactory::__construct(AuthorizationCheckerInterface $authorizationChecker, RouterInterface $router, EntityNameResolver $entityNameResolver, DateTimeFormatterInterface $dateTimeFormatter, AttachmentManager $attachmentManager)`<sup>[[?]](https://github.com/oroinc/crm/tree/4.0.0-rc/src/Oro/Bundle/CaseBundle/Model/ViewFactory.php#L45 "Oro\Bundle\CaseBundle\Model\ViewFactory")</sup>

ChannelBundle
-------------
* The following classes were removed:
   - `EntityExclusionProvider`<sup>[[?]](https://github.com/oroinc/crm/tree/4.0.0-beta/src/Oro/Bundle/ChannelBundle/Provider/EntityExclusionProvider.php#L12 "Oro\Bundle\ChannelBundle\Provider\EntityExclusionProvider")</sup>
   - `ChannelConfiguration`<sup>[[?]](https://github.com/oroinc/crm/tree/4.0.0-beta/src/Oro/Bundle/ChannelBundle/DependencyInjection/ChannelConfiguration.php#L9 "Oro\Bundle\ChannelBundle\DependencyInjection\ChannelConfiguration")</sup>
   - `SettingsPass`<sup>[[?]](https://github.com/oroinc/crm/tree/4.0.0-beta/src/Oro/Bundle/ChannelBundle/DependencyInjection/CompilerPass/SettingsPass.php#L12 "Oro\Bundle\ChannelBundle\DependencyInjection\CompilerPass\SettingsPass")</sup>
* The following methods in class `SettingsProvider`<sup>[[?]](https://github.com/oroinc/crm/tree/4.0.0-beta/src/Oro/Bundle/ChannelBundle/Provider/SettingsProvider.php#L41 "Oro\Bundle\ChannelBundle\Provider\SettingsProvider")</sup> were removed:
   - `getSettings`<sup>[[?]](https://github.com/oroinc/crm/tree/4.0.0-beta/src/Oro/Bundle/ChannelBundle/Provider/SettingsProvider.php#L41 "Oro\Bundle\ChannelBundle\Provider\SettingsProvider::getSettings")</sup>
   - `getDependentEntityData`<sup>[[?]](https://github.com/oroinc/crm/tree/4.0.0-beta/src/Oro/Bundle/ChannelBundle/Provider/SettingsProvider.php#L110 "Oro\Bundle\ChannelBundle\Provider\SettingsProvider::getDependentEntityData")</sup>
   - `isChannelSystem`<sup>[[?]](https://github.com/oroinc/crm/tree/4.0.0-beta/src/Oro/Bundle/ChannelBundle/Provider/SettingsProvider.php#L234 "Oro\Bundle\ChannelBundle\Provider\SettingsProvider::isChannelSystem")</sup>
   - `getChannelTypeConfig`<sup>[[?]](https://github.com/oroinc/crm/tree/4.0.0-beta/src/Oro/Bundle/ChannelBundle/Provider/SettingsProvider.php#L295 "Oro\Bundle\ChannelBundle\Provider\SettingsProvider::getChannelTypeConfig")</sup>
* The `ChannelTypeSubscriber::getDatasourceModifierClosure`<sup>[[?]](https://github.com/oroinc/crm/tree/4.0.0-beta/src/Oro/Bundle/ChannelBundle/Form/EventListener/ChannelTypeSubscriber.php#L153 "Oro\Bundle\ChannelBundle\Form\EventListener\ChannelTypeSubscriber::getDatasourceModifierClosure")</sup> method was removed.
* The `SettingsProvider::__construct(array $settings, ResolverInterface $resolver)`<sup>[[?]](https://github.com/oroinc/crm/tree/4.0.0-beta/src/Oro/Bundle/ChannelBundle/Provider/SettingsProvider.php#L28 "Oro\Bundle\ChannelBundle\Provider\SettingsProvider")</sup> method was changed to `SettingsProvider::__construct(ChannelConfigurationProvider $configProvider)`<sup>[[?]](https://github.com/oroinc/crm/tree/4.0.0-rc/src/Oro/Bundle/ChannelBundle/Provider/SettingsProvider.php#L23 "Oro\Bundle\ChannelBundle\Provider\SettingsProvider")</sup>
* The following properties in class `SettingsProvider`<sup>[[?]](https://github.com/oroinc/crm/tree/4.0.0-beta/src/Oro/Bundle/ChannelBundle/Provider/SettingsProvider.php#L13 "Oro\Bundle\ChannelBundle\Provider\SettingsProvider")</sup> were removed:
   - `$settings`<sup>[[?]](https://github.com/oroinc/crm/tree/4.0.0-beta/src/Oro/Bundle/ChannelBundle/Provider/SettingsProvider.php#L13 "Oro\Bundle\ChannelBundle\Provider\SettingsProvider::$settings")</sup>
   - `$resolvedSettings`<sup>[[?]](https://github.com/oroinc/crm/tree/4.0.0-beta/src/Oro/Bundle/ChannelBundle/Provider/SettingsProvider.php#L16 "Oro\Bundle\ChannelBundle\Provider\SettingsProvider::$resolvedSettings")</sup>
   - `$resolver`<sup>[[?]](https://github.com/oroinc/crm/tree/4.0.0-beta/src/Oro/Bundle/ChannelBundle/Provider/SettingsProvider.php#L19 "Oro\Bundle\ChannelBundle\Provider\SettingsProvider::$resolver")</sup>
   - `$dependentEntitiesHashMap`<sup>[[?]](https://github.com/oroinc/crm/tree/4.0.0-beta/src/Oro/Bundle/ChannelBundle/Provider/SettingsProvider.php#L22 "Oro\Bundle\ChannelBundle\Provider\SettingsProvider::$dependentEntitiesHashMap")</sup>

MagentoBundle
-------------
* The `OrderDataProvider::__construct(ManagerRegistry $registry, AclHelper $aclHelper, ConfigProvider $configProvider, DateTimeFormatter $dateTimeFormatter, DateHelper $dateHelper)`<sup>[[?]](https://github.com/oroinc/crm/tree/4.0.0-beta/src/Oro/Bundle/MagentoBundle/Dashboard/OrderDataProvider.php#L49 "Oro\Bundle\MagentoBundle\Dashboard\OrderDataProvider")</sup> method was changed to `OrderDataProvider::__construct(ManagerRegistry $registry, AclHelper $aclHelper, ConfigProvider $configProvider, DateTimeFormatterInterface $dateTimeFormatter, DateHelper $dateHelper)`<sup>[[?]](https://github.com/oroinc/crm/tree/4.0.0-rc/src/Oro/Bundle/MagentoBundle/Dashboard/OrderDataProvider.php#L52 "Oro\Bundle\MagentoBundle\Dashboard\OrderDataProvider")</sup>

SalesBundle
-----------
* The `ForecastOfOpportunities::__construct(NumberFormatter $numberFormatter, DateTimeFormatter $dateTimeFormatter, TranslatorInterface $translator, DateHelper $dateHelper, ForecastProvider $provider, FilterDateRangeConverter $filterDateRangeConverter)`<sup>[[?]](https://github.com/oroinc/crm/tree/4.0.0-beta/src/Oro/Bundle/SalesBundle/Provider/ForecastOfOpportunities.php#L49 "Oro\Bundle\SalesBundle\Provider\ForecastOfOpportunities")</sup> method was changed to `ForecastOfOpportunities::__construct(NumberFormatter $numberFormatter, DateTimeFormatterInterface $dateTimeFormatter, TranslatorInterface $translator, DateHelper $dateHelper, ForecastProvider $provider, FilterDateRangeConverter $filterDateRangeConverter)`<sup>[[?]](https://github.com/oroinc/crm/tree/4.0.0-rc/src/Oro/Bundle/SalesBundle/Provider/ForecastOfOpportunities.php#L49 "Oro\Bundle\SalesBundle\Provider\ForecastOfOpportunities")</sup>

