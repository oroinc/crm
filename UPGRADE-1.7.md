UPGRADE FROM 1.6 to 1.7
=======================

####OroCRMContactUsBundle:
- Migrated the embedded form templates to the new layout update mechanism introduced by the **OroLayoutBundle**.
Existing customizations of the embedded form layout by overriding the twig template in `app/Resources` will no longer work.
The twig template no longer holds the markup for the whole form but rather markup for separate blocks defined by the layouts engine.

####OroCRMMagentoBundle:
- To abstract class `OroCRM\Bundle\MagentoBundle\Provider\AbstractMagentoConnector` added required dependency from \Doctrine\Common\Persistence\ManagerRegistry. 
To inject dependency use method setManagerRegistry. This change affects all derivative classes and next services in DIC: `orocrm_magento.mage.customer_connector`, `orocrm_magento.mage.cart_connector`, `orocrm_magento.mage.order_connector`, `orocrm_magento.mage.region_connector`.
- Added `orocrm_magento.initial_import_step_interval` setting. Default interval set to 7 days.
- `OroCRM\Bundle\MagentoBundle\Entity\Customer` implements new interface `SyncStateAwareInterface`
- `OroCRM\Bundle\MagentoBundle\Entity\Order` implements new interface `SyncStateAwareInterface`
- Three new methods were added to `OroCRM\Bundle\MagentoBundle\Provider\Iterator\UpdatedLoaderInterface`: `setSyncRange(\DateInterval $syncRange)`, `setMinSyncDate(\DateTime $date)`, `setEntitiesIdsBuffer(array $entitiesIdsBuffer)`
- `OroCRM\Bundle\MagentoBundle\Provider\Transport\MagentoTransportInterface` was updated. Added `getCustomerInfo(Customer $customer)`, `getOrderInfo($incrementId)`, `getDependencies(array $dependenciesToLoad = null, $force = false)`
- `OroCRM\Bundle\MagentoBundle\Provider\Transport\SoapTransport` updated according to `OroCRM\Bundle\MagentoBundle\Provider\Transport\MagentoTransportInterface` changes
- Removed `OroCRM\Bundle\MagentoBundle\Converter\RegionConverter`
- Removed `OroCRM\Bundle\MagentoBundle\EventListener\ContactListener`
- Removed `OroCRM\Bundle\MagentoBundle\ImportExport\Processor\AbstractReverseProcessor`
- Removed `OroCRM\Bundle\MagentoBundle\ImportExport\Processor\CustomerReverseProcessor`
- Removed `OroCRM\Bundle\MagentoBundle\ImportExport\Serializer\CartAddressNormalizer`
- Removed `OroCRM\Bundle\MagentoBundle\ImportExport\Serializer\CartItemCompositeDenormalizer`
- Removed `OroCRM\Bundle\MagentoBundle\ImportExport\Serializer\CustomerSerializer`
- Removed `OroCRM\Bundle\MagentoBundle\ImportExport\Serializer\MagentoAddressNormalizer`
- Removed `OroCRM\Bundle\MagentoBundle\ImportExport\Serializer\CompositeNormalizer`
- Removed `OroCRM\Bundle\MagentoBundle\ImportExport\Serializer\OrderAddressCompositeDenormalizer`
- Removed `OroCRM\Bundle\MagentoBundle\ImportExport\Strategy\StrategyHelper\ContactImportHelper`
- Removed `OroCRM\Bundle\MagentoBundle\ImportExport\Writer\ReverseWriter`

- `OroCRM\Bundle\MagentoBundle\ImportExport\Serializer\OrderItemCompositeDenormalizer` renamed to `OroCRM\Bundle\MagentoBundle\ImportExport\Serializer\OrderItemDenormalizer`
- `OroCRM\Bundle\MagentoBundle\ImportExport\Converter\MagentoAddressDataConverter` renamed to `OroCRM\Bundle\MagentoBundle\ImportExport\Converter\CustomerAddressDataConverter`
- `OroCRM\Bundle\MagentoBundle\ImportExport\Converter\AddressDataConverter` moved to `OroCRM\Bundle\MagentoBundle\ImportExport\Converter\AbstractAddressDataConverter`

- In namespace `OroCRM\Bundle\MagentoBundle\Entity` all entities that used `OriginTrait` now also implements `OroCRM\Bundle\MagentoBundle\Entity\OriginAwareInterface`
- In namespace `OroCRM\Bundle\MagentoBundle\Entity` all entities that used `IntegrationEntityTrait` now also implements `OroCRM\Bundle\MagentoBundle\Entity\IntegrationAwareInterface`
- `OroCRM\Bundle\MagentoBundle\Entity\Address` added usage of `IntegrationEntityTrait`

- Magento Customer import now works on top of Data Converters and Strategy
- Composite denormalizers removed in favour of TreeDataConverters

- Added `oro:magento:lifetime:recalculate` CLI command that can be used to force recalculation of lifetime values for Magento customers and accounts

####OroCRMSalesBundle:
- The `calculateLifetime` method of `OroCRM\Bundle\SalesBundle\Entity\Repository\B2bCustomerRepository` is deprecated. Use `calculateLifetimeValue` method instead
- Added `oro:b2b:lifetime:recalculate` CLI command that can be used to force recalculation of lifetime values for B2B customers and accounts
