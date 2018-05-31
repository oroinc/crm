- [ChannelBundle](#channelbundle)
- [MagentoBundle](#magentobundle)

ChannelBundle
-------------
* The `StateProvider::ensureLocalCacheWarmed`<sup>[[?]](https://github.com/oroinc/crm/tree/3.0.0-beta/src/Oro/Bundle/ChannelBundle/Provider/StateProvider.php#L116 "Oro\Bundle\ChannelBundle\Provider\StateProvider::ensureLocalCacheWarmed")</sup> method was removed.
* The `ChannelTypeSubscriber::getFirstChannelType`<sup>[[?]](https://github.com/oroinc/crm/tree/3.0.0-beta/src/Oro/Bundle/ChannelBundle/Form/EventListener/ChannelTypeSubscriber.php#L177 "Oro\Bundle\ChannelBundle\Form\EventListener\ChannelTypeSubscriber::getFirstChannelType")</sup> method was removed.
* The following methods in class `StateProvider`<sup>[[?]](https://github.com/oroinc/crm/tree/3.0.0-rc/src/Oro/Bundle/ChannelBundle/Provider/StateProvider.php#L36 "Oro\Bundle\ChannelBundle\Provider\StateProvider")</sup> were changed:
  > - `__construct(SettingsProvider $settingsProvider, Cache $cache, RegistryInterface $registry, TokenAccessorInterface $tokenAccessor, AclHelper $aclHelper)`<sup>[[?]](https://github.com/oroinc/crm/tree/3.0.0-beta/src/Oro/Bundle/ChannelBundle/Provider/StateProvider.php#L40 "Oro\Bundle\ChannelBundle\Provider\StateProvider")</sup>
  > - `__construct(SettingsProvider $settingsProvider, Cache $cache, RegistryInterface $registry, TokenAccessorInterface $tokenAccessor)`<sup>[[?]](https://github.com/oroinc/crm/tree/3.0.0-rc/src/Oro/Bundle/ChannelBundle/Provider/StateProvider.php#L36 "Oro\Bundle\ChannelBundle\Provider\StateProvider")</sup>

  > - `persistToCache()`<sup>[[?]](https://github.com/oroinc/crm/tree/3.0.0-beta/src/Oro/Bundle/ChannelBundle/Provider/StateProvider.php#L166 "Oro\Bundle\ChannelBundle\Provider\StateProvider")</sup>
  > - `persistToCache(array $enabledEntities)`<sup>[[?]](https://github.com/oroinc/crm/tree/3.0.0-rc/src/Oro/Bundle/ChannelBundle/Provider/StateProvider.php#L184 "Oro\Bundle\ChannelBundle\Provider\StateProvider")</sup>

* The following properties in class `StateProvider`<sup>[[?]](https://github.com/oroinc/crm/tree/3.0.0-beta/src/Oro/Bundle/ChannelBundle/Provider/StateProvider.php#L25 "Oro\Bundle\ChannelBundle\Provider\StateProvider")</sup> were removed:
   - `$enabledEntities`<sup>[[?]](https://github.com/oroinc/crm/tree/3.0.0-beta/src/Oro/Bundle/ChannelBundle/Provider/StateProvider.php#L25 "Oro\Bundle\ChannelBundle\Provider\StateProvider::$enabledEntities")</sup>
   - `$aclHelper`<sup>[[?]](https://github.com/oroinc/crm/tree/3.0.0-beta/src/Oro/Bundle/ChannelBundle/Provider/StateProvider.php#L28 "Oro\Bundle\ChannelBundle\Provider\StateProvider::$aclHelper")</sup>

MagentoBundle
-------------
* The following methods in class `AccountProvider`<sup>[[?]](https://github.com/oroinc/crm/tree/3.0.0-beta/src/Oro/Bundle/MagentoBundle/Provider/Customer/AccountProvider.php#L32 "Oro\Bundle\MagentoBundle\Provider\Customer\AccountProvider")</sup> were removed:
   - `setContainer`<sup>[[?]](https://github.com/oroinc/crm/tree/3.0.0-beta/src/Oro/Bundle/MagentoBundle/Provider/Customer/AccountProvider.php#L32 "Oro\Bundle\MagentoBundle\Provider\Customer\AccountProvider::setContainer")</sup>
   - `getAutomaticDiscovery`<sup>[[?]](https://github.com/oroinc/crm/tree/3.0.0-beta/src/Oro/Bundle/MagentoBundle/Provider/Customer/AccountProvider.php#L104 "Oro\Bundle\MagentoBundle\Provider\Customer\AccountProvider::getAutomaticDiscovery")</sup>
* The `AccountProvider::__construct(NewEntitiesHelper $newEntitiesHelper)`<sup>[[?]](https://github.com/oroinc/crm/tree/3.0.0-beta/src/Oro/Bundle/MagentoBundle/Provider/Customer/AccountProvider.php#L24 "Oro\Bundle\MagentoBundle\Provider\Customer\AccountProvider")</sup> method was changed to `AccountProvider::__construct(NewEntitiesHelper $newEntitiesHelper, AutomaticDiscovery $automaticDiscovery, ManagerRegistry $registry)`<sup>[[?]](https://github.com/oroinc/crm/tree/3.0.0-rc/src/Oro/Bundle/MagentoBundle/Provider/Customer/AccountProvider.php#L35 "Oro\Bundle\MagentoBundle\Provider\Customer\AccountProvider")</sup>
* The `CustomerGroupSelectType::isReadOnly($options)`<sup>[[?]](https://github.com/oroinc/crm/tree/3.0.0-beta/src/Oro/Bundle/MagentoBundle/Form/Type/CustomerGroupSelectType.php#L87 "Oro\Bundle\MagentoBundle\Form\Type\CustomerGroupSelectType")</sup> method was changed to `CustomerGroupSelectType::isReadOnly()`<sup>[[?]](https://github.com/oroinc/crm/tree/3.0.0-rc/src/Oro/Bundle/MagentoBundle/Form/Type/CustomerGroupSelectType.php#L87 "Oro\Bundle\MagentoBundle\Form\Type\CustomerGroupSelectType")</sup>
* The `AccountProvider::$container`<sup>[[?]](https://github.com/oroinc/crm/tree/3.0.0-beta/src/Oro/Bundle/MagentoBundle/Provider/Customer/AccountProvider.php#L16 "Oro\Bundle\MagentoBundle\Provider\Customer\AccountProvider::$container")</sup> property was removed.

