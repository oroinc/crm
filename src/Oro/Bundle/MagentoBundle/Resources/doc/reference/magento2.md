Magento 2
=========

Table of Contents
-----------------
 - [How to Enable Magento 2 Integration](#how-to-enable-magento-2-integration)

How to Enable Magento 2 Integration
-----------------------------------
By default, Magento 2 integration channel is disabled.

To enable it, create a
channels.yml file in Resource\config\oro in a custom bundle with the
following data:

```yaml
channels:
   channel_types:
        magento2:
            label: oro.magento.channel_type.magento2.label
            entities:
                - Oro\Bundle\MagentoBundle\Entity\Website
            integration_type: magento2
            customer_identity: Oro\Bundle\ChannelBundle\Entity\CustomerIdentity
```

And register following service in services.yml

```yaml
oro_magento.provider.magento2_channel_type:
    class: Oro\Bundle\MagentoBundle\Provider\Magento2ChannelType
    tags:
        - { name: oro_integration.channel, type: magento2 }
```
