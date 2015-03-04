UPGRADE FROM 1.6 to 1.7
=======================

####OroCRMMagentoBundle:
- To abstract class `OroCRM\Bundle\MagentoBundle\Provider\AbstractMagentoConnector` added required dependency from \Doctrine\Common\Persistence\ManagerRegistry. 
To inject dependency use method setManagerRegistry. This change affects all derivative classes and next services in DIC: `orocrm_magento.mage.customer_connector`, `orocrm_magento.mage.cart_connector`, `orocrm_magento.mage.order_connector`, `orocrm_magento.mage.region_connector`.

####OroCRMContactUsBundle:
- Migrated the embedded form templates to the new layout update mechanism introduced by the **OroLayoutBundle**.
Existing customizations of the embedded form layout by overriding the twig template in `app/Resources` will no longer work.
The twig template no longer holds the markup for the whole form but rather markup for separate blocks defined by the layouts engine.
