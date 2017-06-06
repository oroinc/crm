UPGRADE FROM 2.2 to 2.3
========================

MagentoBundle
-------------
- Class `OroCRM\Bundle\MagentoBundle\Provider\Transport\MagentoTransportInterface`
    - Added methods `getCreditMemos()`, `getCreditMemoInfo($incrementId)`.
- Class `OroCRM\Bundle\MagentoBundle\Provider\Transport\SoapTransport` 
    - Updated according to `OroCRM\Bundle\MagentoBundleProvider\Transport\MagentoTransportInterface` changes.
- Class `Oro\Bundle\MagentoBundle\Entity\Order`
    - field `originId` added
    - `Oro\Bundle\MagentoBundle\Entity\OriginTrait` used
