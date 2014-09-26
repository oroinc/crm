UPGRADE FROM 1.3 to 1.4
=======================

####AccountBundle:
- `Entity\Account` attributes `shippingAddress`, `billingAddress` have been removed
- In `Form\Type\AccountType` fields `shippingAddress`, `billingAddress` also have been removed
- Virtual field `lifetimeValue` has been added into `Entity\Account`

####ContactUsBundle:
- `Form\Type\ContactRequestType` field `dataChannel` has been included

####MagentoBundle:
- `Entity\Cart`, `Entity\Customer` and `Entity\Order` now implement `ChannelAwareInterface` and use `ChannelEntityTrait`
- `Form\Type\WebsiteSelectType` has been added
- `Service\ImportHelper` method `getChannelFromContext` has been renamed to `getIntegrationFromContext`

####SalesBundle:
- `Entity\Leads` and `Entity\Opportunities` no longer have relationship with `AccountBundle\Entity\Account` this field has been removed
- `Entity\Leads` and `Entity\Opportunities` currently have relationship with `Entity\B2bCustomer`
- `Entity\Leads`, `Entity\Opportunities` and `Entity\SalesFunnel` currently implements `ChannelAwareInterface` and use `ChannelEntityTrait`
