UPGRADE FROM 1.6 to 1.7
=======================

####OroCRMMagentoBundle:
- To abstract class `OroCRM\Bundle\MagentoBundle\Provider\AbstractMagentoConnector` added required dependency from \Doctrine\Common\Persistence\ManagerRegistry. 
To inject dependency use method setManagerRegistry. This change affects all derivative classes and next services in DIC: `orocrm_magento.mage.customer_connector`, `orocrm_magento.mage.cart_connector`, `orocrm_magento.mage.order_connector`, `orocrm_magento.mage.region_connector`.
