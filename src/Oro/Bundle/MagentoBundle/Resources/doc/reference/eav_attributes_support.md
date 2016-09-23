Magento EAV attributes support
==============================

Magento integration support automatic mapping of Magento EAV attributes to Oro entities.
Supported entities are:
 
 - Magento Customer
 - Magento Customer Address
 - Magento Order
 - Magento Shopping Cart (Quote)
 
To enable import of EAV attributes next steps should be performed:

 - Latest version of [Oro Bridge](http://www.magentocommerce.com/magento-connect/orocrm-bridge.html) 
    extension must be installed
 - Enable attributes on magento side. To do this go to System -> Config, there in Customer section 
    choose Oro API. In API subsection change Enable Attributes to Yes
 - In Oro go to Integration settings and click Check connection button. If connection is ok save integration.
 
After performing of this steps EAV attributes exposed to Oro. Them are mapped to entity fields by name.
Note! Entity field name must be same to EAV attribute in magento but in camelCase. Field type must match according EAV attribute type.
Only Table column Storage Type is supported.
Entity fields may be added as extended field or with migration script.

Example:
In magento EAV attribute named *some_attribute*.
In Oro field name should be named *someAttribute*

Only scalar attributes are supported.
