- [ContactBundle](#contactbundle)
- [MagentoBundle](#magentobundle)
- [SalesBundle](#salesbundle)

ContactBundle
-------------
* The `ContactListener::isContactEntity`<sup>[[?]](https://github.com/oroinc/crm/tree/2.5.0/src/Oro/Bundle/ContactBundle/EventListener/ContactListener.php#L61 "Oro\Bundle\ContactBundle\EventListener\ContactListener::isContactEntity")</sup> method was removed.
* The following methods in class `ContactListener`<sup>[[?]](https://github.com/oroinc/crm/tree/2.6.0/src/Oro/Bundle/ContactBundle/EventListener/ContactListener.php#L32 "Oro\Bundle\ContactBundle\EventListener\ContactListener")</sup> were changed:
  > - `prePersist(LifecycleEventArgs $args)`<sup>[[?]](https://github.com/oroinc/crm/tree/2.5.0/src/Oro/Bundle/ContactBundle/EventListener/ContactListener.php#L31 "Oro\Bundle\ContactBundle\EventListener\ContactListener")</sup>
  > - `prePersist(Contact $entity, LifecycleEventArgs $args)`<sup>[[?]](https://github.com/oroinc/crm/tree/2.6.0/src/Oro/Bundle/ContactBundle/EventListener/ContactListener.php#L32 "Oro\Bundle\ContactBundle\EventListener\ContactListener")</sup>

  > - `preUpdate(PreUpdateEventArgs $args)`<sup>[[?]](https://github.com/oroinc/crm/tree/2.5.0/src/Oro/Bundle/ContactBundle/EventListener/ContactListener.php#L46 "Oro\Bundle\ContactBundle\EventListener\ContactListener")</sup>
  > - `preUpdate(Contact $entity, PreUpdateEventArgs $args)`<sup>[[?]](https://github.com/oroinc/crm/tree/2.6.0/src/Oro/Bundle/ContactBundle/EventListener/ContactListener.php#L42 "Oro\Bundle\ContactBundle\EventListener\ContactListener")</sup>


MagentoBundle
-------------
* The `CustomerCurrencyListener::setContainer`<sup>[[?]](https://github.com/oroinc/crm/tree/2.5.0/src/Oro/Bundle/MagentoBundle/EventListener/CustomerCurrencyListener.php#L28 "Oro\Bundle\MagentoBundle\EventListener\CustomerCurrencyListener::setContainer")</sup> method was removed.
* The `CustomerCurrencyListener::prePersist(LifecycleEventArgs $event)`<sup>[[?]](https://github.com/oroinc/crm/tree/2.5.0/src/Oro/Bundle/MagentoBundle/EventListener/CustomerCurrencyListener.php#L36 "Oro\Bundle\MagentoBundle\EventListener\CustomerCurrencyListener")</sup> method was changed to `CustomerCurrencyListener::prePersist(Customer $entity, LifecycleEventArgs $event)`<sup>[[?]](https://github.com/oroinc/crm/tree/2.6.0/src/Oro/Bundle/MagentoBundle/EventListener/CustomerCurrencyListener.php#L29 "Oro\Bundle\MagentoBundle\EventListener\CustomerCurrencyListener")</sup>
* The `IntegrationRemoveListener::preRemove(LifecycleEventArgs $eventArgs)`<sup>[[?]](https://github.com/oroinc/crm/tree/2.5.0/src/Oro/Bundle/MagentoBundle/EventListener/IntegrationRemoveListener.php#L31 "Oro\Bundle\MagentoBundle\EventListener\IntegrationRemoveListener")</sup> method was changed to `IntegrationRemoveListener::preRemove(MagentoSoapTransport $entity, LifecycleEventArgs $args)`<sup>[[?]](https://github.com/oroinc/crm/tree/2.6.0/src/Oro/Bundle/MagentoBundle/EventListener/IntegrationRemoveListener.php#L32 "Oro\Bundle\MagentoBundle\EventListener\IntegrationRemoveListener")</sup>
* The following methods in class `OrderListener`<sup>[[?]](https://github.com/oroinc/crm/tree/2.6.0/src/Oro/Bundle/MagentoBundle/EventListener/OrderListener.php#L37 "Oro\Bundle\MagentoBundle\EventListener\OrderListener")</sup> were changed:
  > - `prePersist(LifecycleEventArgs $event)`<sup>[[?]](https://github.com/oroinc/crm/tree/2.5.0/src/Oro/Bundle/MagentoBundle/EventListener/OrderListener.php#L36 "Oro\Bundle\MagentoBundle\EventListener\OrderListener")</sup>
  > - `prePersist(Order $entity, LifecycleEventArgs $event)`<sup>[[?]](https://github.com/oroinc/crm/tree/2.6.0/src/Oro/Bundle/MagentoBundle/EventListener/OrderListener.php#L37 "Oro\Bundle\MagentoBundle\EventListener\OrderListener")</sup>

  > - `preUpdate(PreUpdateEventArgs $event)`<sup>[[?]](https://github.com/oroinc/crm/tree/2.5.0/src/Oro/Bundle/MagentoBundle/EventListener/OrderListener.php#L54 "Oro\Bundle\MagentoBundle\EventListener\OrderListener")</sup>
  > - `preUpdate(Order $entity, PreUpdateEventArgs $event)`<sup>[[?]](https://github.com/oroinc/crm/tree/2.6.0/src/Oro/Bundle/MagentoBundle/EventListener/OrderListener.php#L53 "Oro\Bundle\MagentoBundle\EventListener\OrderListener")</sup>

* The `CustomerCurrencyListener::$localeSettings`<sup>[[?]](https://github.com/oroinc/crm/tree/2.5.0/src/Oro/Bundle/MagentoBundle/EventListener/CustomerCurrencyListener.php#L18 "Oro\Bundle\MagentoBundle\EventListener\CustomerCurrencyListener::$localeSettings")</sup> property was removed.
* The `OrderListener::isOrderValid`<sup>[[?]](https://github.com/oroinc/crm/tree/2.5.0/src/Oro/Bundle/MagentoBundle/EventListener/OrderListener.php#L121 "Oro\Bundle\MagentoBundle\EventListener\OrderListener::isOrderValid")</sup> method was removed.
* The following methods in interface `MagentoTransportInterface`<sup>[[?]](https://github.com/oroinc/crm/tree/2.6.0/src/Oro/Bundle/MagentoBundle/Provider/Transport/MagentoTransportInterface.php#L240 "Oro\Bundle\MagentoBundle\Provider\Transport\MagentoTransportInterface")</sup> were added:
   - `isSupportedOrderNoteExtensionVersion`<sup>[[?]](https://github.com/oroinc/crm/tree/2.6.0/src/Oro/Bundle/MagentoBundle/Provider/Transport/MagentoTransportInterface.php#L240 "Oro\Bundle\MagentoBundle\Provider\Transport\MagentoTransportInterface::isSupportedOrderNoteExtensionVersion")</sup>
   - `getOrderNoteRequiredExtensionVersion`<sup>[[?]](https://github.com/oroinc/crm/tree/2.6.0/src/Oro/Bundle/MagentoBundle/Provider/Transport/MagentoTransportInterface.php#L268 "Oro\Bundle\MagentoBundle\Provider\Transport\MagentoTransportInterface::getOrderNoteRequiredExtensionVersion")</sup>

SalesBundle
-----------
* The `DefaultProbabilityListener::preUpdate(PreUpdateEventArgs $args)`<sup>[[?]](https://github.com/oroinc/crm/tree/2.5.0/src/Oro/Bundle/SalesBundle/EventListener/DefaultProbabilityListener.php#L33 "Oro\Bundle\SalesBundle\EventListener\DefaultProbabilityListener")</sup> method was changed to `DefaultProbabilityListener::preUpdate(Opportunity $entity, PreUpdateEventArgs $args)`<sup>[[?]](https://github.com/oroinc/crm/tree/2.6.0/src/Oro/Bundle/SalesBundle/EventListener/DefaultProbabilityListener.php#L34 "Oro\Bundle\SalesBundle\EventListener\DefaultProbabilityListener")</sup>

