UPGRADE FROM 1.6 to 1.7
=======================

####OroCRMMagentoBundle:
- To abstract class `OroCRM\Bundle\MagentoBundle\Provider\AbstractMagentoConnector` added required dependency from \Doctrine\Common\Persistence\ManagerRegistry. 
To inject dependency use method setManagerRegistry. This change affects all derivative classes and next services in DIC: `orocrm_magento.mage.customer_connector`, `orocrm_magento.mage.cart_connector`, `orocrm_magento.mage.order_connector`, `orocrm_magento.mage.region_connector`.
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
