- [AccountBundle](#accountbundle)
- [ChannelBundle](#channelbundle)
- [ContactBundle](#contactbundle)
- [SalesBundle](#salesbundle)

AccountBundle
-------------
* The `OroAccountExtension::getAlias`<sup>[[?]](https://github.com/oroinc/crm/tree/5.0.0/src/Oro/Bundle/AccountBundle/DependencyInjection/OroAccountExtension.php#L31 "Oro\Bundle\AccountBundle\DependencyInjection\OroAccountExtension::getAlias")</sup> method was removed.

ChannelBundle
-------------
* The following methods in class `StateProvider`<sup>[[?]](https://github.com/oroinc/crm/tree/5.0.0/src/Oro/Bundle/ChannelBundle/Provider/StateProvider.php#L164 "Oro\Bundle\ChannelBundle\Provider\StateProvider")</sup> were removed:
   - `tryCacheLookUp`<sup>[[?]](https://github.com/oroinc/crm/tree/5.0.0/src/Oro/Bundle/ChannelBundle/Provider/StateProvider.php#L164 "Oro\Bundle\ChannelBundle\Provider\StateProvider::tryCacheLookUp")</sup>
   - `persistToCache`<sup>[[?]](https://github.com/oroinc/crm/tree/5.0.0/src/Oro/Bundle/ChannelBundle/Provider/StateProvider.php#L174 "Oro\Bundle\ChannelBundle\Provider\StateProvider::persistToCache")</sup>
* The `StateProvider::__construct(SettingsProvider $settingsProvider, Cache $cache, ManagerRegistry $registry, TokenAccessorInterface $tokenAccessor)`<sup>[[?]](https://github.com/oroinc/crm/tree/5.0.0/src/Oro/Bundle/ChannelBundle/Provider/StateProvider.php#L30 "Oro\Bundle\ChannelBundle\Provider\StateProvider")</sup> method was changed to `StateProvider::__construct(SettingsProvider $settingsProvider, CacheInterface $cache, ManagerRegistry $registry, TokenAccessorInterface $tokenAccessor)`<sup>[[?]](https://github.com/oroinc/crm/tree/5.1.0-alpha/src/Oro/Bundle/ChannelBundle/Provider/StateProvider.php#L23 "Oro\Bundle\ChannelBundle\Provider\StateProvider")</sup>

ContactBundle
-------------
* The `PrepareResultItemListener`<sup>[[?]](https://github.com/oroinc/crm/tree/5.0.0/src/Oro/Bundle/ContactBundle/EventListener/PrepareResultItemListener.php#L13 "Oro\Bundle\ContactBundle\EventListener\PrepareResultItemListener")</sup> class was removed.
* The `EmailOwnerProvider::findEmailOwner(EntityManager $em, $email)`<sup>[[?]](https://github.com/oroinc/crm/tree/5.0.0/src/Oro/Bundle/ContactBundle/Entity/Provider/EmailOwnerProvider.php#L23 "Oro\Bundle\ContactBundle\Entity\Provider\EmailOwnerProvider")</sup> method was changed to `EmailOwnerProvider::findEmailOwner(EntityManagerInterface $em, $email)`<sup>[[?]](https://github.com/oroinc/crm/tree/5.1.0-alpha/src/Oro/Bundle/ContactBundle/Entity/Provider/EmailOwnerProvider.php#L29 "Oro\Bundle\ContactBundle\Entity\Provider\EmailOwnerProvider")</sup>

SalesBundle
-----------
* The `ConfigCache::__construct(CacheProvider $cache)`<sup>[[?]](https://github.com/oroinc/crm/tree/5.0.0/src/Oro/Bundle/SalesBundle/Provider/Customer/ConfigCache.php#L15 "Oro\Bundle\SalesBundle\Provider\Customer\ConfigCache")</sup> method was changed to `ConfigCache::__construct(CacheItemPoolInterface $cache)`<sup>[[?]](https://github.com/oroinc/crm/tree/5.1.0-alpha/src/Oro/Bundle/SalesBundle/Provider/Customer/ConfigCache.php#L16 "Oro\Bundle\SalesBundle\Provider\Customer\ConfigCache")</sup>
* The `AccountCustomerManager::getCustomerTargetField($targetClassName)`<sup>[[?]](https://github.com/oroinc/crm/tree/5.0.0/src/Oro/Bundle/SalesBundle/Entity/Manager/AccountCustomerManager.php#L43 "Oro\Bundle\SalesBundle\Entity\Manager\AccountCustomerManager")</sup> method was changed to `AccountCustomerManager::getCustomerTargetField($targetClassName)`<sup>[[?]](https://github.com/oroinc/crm/tree/5.1.0-alpha/src/Oro/Bundle/SalesBundle/Entity/Manager/AccountCustomerManager.php#L39 "Oro\Bundle\SalesBundle\Entity\Manager\AccountCustomerManager")</sup>
* The `AccountCustomerManager::createCustomer`<sup>[[?]](https://github.com/oroinc/crm/tree/5.0.0/src/Oro/Bundle/SalesBundle/Entity/Manager/AccountCustomerManager.php#L60 "Oro\Bundle\SalesBundle\Entity\Manager\AccountCustomerManager::createCustomer")</sup> method was removed.

