# OroMagentoBundle

OroMagentoBundle enables integration with Magento e-commerce solution in Oro applications.

The bundle allows admin users to create and configure Magento [channels](https://github.com/oroinc/crm/tree/master/src/Oro/Bundle/ChannelBundle) and [integrations](https://github.com/oroinc/platform/tree/master/src/Oro/Bundle/IntegrationBundle) to synchronize customers contacts, orders, carts, credit memos, and newsletters subscribers data between Magento and Oro applications.

### Table of contents

* [B2C Workflows](./Resources/doc/reference/workflows.md)
* [Automatic accounts discovery](./Resources/doc/reference/account_discovery.md)
* [EAV attributes support](./Resources/doc/reference/eav_attributes_support.md)

### Notes

In case of using this bundle OroMagentoBundle without OroBridge extension change_status_at date for
NewsletterSubscriber will be empty during import because of bug on Magento side
