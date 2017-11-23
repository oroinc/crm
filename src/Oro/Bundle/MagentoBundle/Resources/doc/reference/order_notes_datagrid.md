Order Notes Datagrids
============================

Configuration for four datagrids was added to [datagrids.yml](../../config/oro/datagrids.yml)

## magento-order-notes-base-grid
The base grid that contains the general configuration for the "Order Notes" datagrid. It is used only as parent grid. Extend this grid if you need a custom implementation of the "Order Notes" datagrid.

## magento-order-notes-widget-grid
Is shown on Magento Order View page. Has data on notes attached to the current order.

## magento-account-order-notes-widget-grid
It is shown on Magento Account View page. Has data on notes attached to all orders of the current customer in the selected channel.

## magento-order-notes-widget-grid
It extends `magento-account-order-notes-widget-grid` and is shown on Magento Customer View page. Has data on the notes attached to all orders of the current customer.
