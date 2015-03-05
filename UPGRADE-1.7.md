UPGRADE FROM 1.6 to 1.7
=======================

####OroCRMMagentoBundle:
- To abstract class `OroCRM\Bundle\MagentoBundle\Provider\AbstractMagentoConnector` added required dependency from \Doctrine\Common\Persistence\ManagerRegistry. 
To inject dependency use method setManagerRegistry. This change affects all derivative classes and next services in DIC: `orocrm_magento.mage.customer_connector`, `orocrm_magento.mage.cart_connector`, `orocrm_magento.mage.order_connector`, `orocrm_magento.mage.region_connector`.
- Added `orocrm_magento.initial_import_step_interval` setting. Default interval set to 7 days.
- `OroCRM\Bundle\MagentoBundle\Entity\Customer` implements new interface `SyncStateAwareInterface`
- `OroCRM\Bundle\MagentoBundle\Entity\Order` implements new interface `SyncStateAwareInterface`
- Three new methods were added to `OroCRM\Bundle\MagentoBundle\Provider\Iterator\UpdatedLoaderInterface`: `setSyncRange(\DateInterval $syncRange)`, `setMinSyncDate(\DateTime $date)`, `setEntitiesIdsBuffer(array $entitiesIdsBuffer)`
- `OroCRM\Bundle\MagentoBundle\Provider\Transport\MagentoTransportInterface` was updated. Added `getCustomerInfo(Customer $customer)`, `getOrderInfo($incrementId)`, `getDependencies(array $dependenciesToLoad = null, $force = false)`
- `OroCRM\Bundle\MagentoBundle\Provider\Transport\SoapTransport` updated according to `OroCRM\Bundle\MagentoBundle\Provider\Transport\MagentoTransportInterface` changes
