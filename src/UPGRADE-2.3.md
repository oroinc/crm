UPGRADE FROM 2.2 to 2.3
========================

MagentoBundle
-------------
- Interface `Oro\Bundle\MagentoBundle\Provider\Transport\MagentoTransportInterface`
    - removed method `call` because it conflicts with REST conception. MagentoTransportInterface from now wont allow to specify http methods and resource through parameters.
   