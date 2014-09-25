UPGRADE FROM 1.3 to 1.4
=======================

####AccountBundle:
- Remove connection with `IntegrationBundle:Channel` and instead added connection with `ChannelBundle:Channel`
- `Entity\Account` attributes `shippingAddress`, `billingAddress` have been removed
- In `Form\Type\AccountType` fields `shippingAddress`, `billingAddress` also have been removed
- Virtual field `lifetimeValue` has been added into `Entity\Account`

####CampaignBundle:
- `Email Campaigns` has been added
- New command `oro:cron:send-email-campaigns` has been added in `Command\SendEmailCampaignsCommand`
- `Form\Handler\CampaignHandler` has been deleted
- `Form\Type\CampaignSelectType` has been added
- `Form\Type\EmailTransportSelectType` has been added

####ChannelBundle has been added:
- All menu items that including data channel now hidden by default

####ContactUsBundle:
- `Form\Type\ContactRequestType` field `dataChannel` has been included

####MagentoBundle:
- Account has been removed Order
- `Entity\Cart`, `Entity\Customer` and `Entity\Order` now implement `ChannelAwareInterface` and use `ChannelEntityTrait`
- `Form\Type\WebsiteSelectType` has been added
- `Service\ImportHelper` method `getChannelFromContext` has been renamed to `getIntegrationFromContext~

####MarketingListBundle has been added

####SalesBundle:
- Added new entity `Entity\B2BCustomer`
- `Entity\Leads` and `Entity\Opportunities` no longer have relationship with `AccountBundle\Entity\Account` this field has been removed
- `Entity\Leads` and `Entity\Opportunities` currently have relationship with `Entity\B2bCustomer`
- `Entity\Leads`, `Entity\Opportunities` and `Entity\SalesFunnel` currently implements `ChannelAwareInterface` and use `ChannelEntityTrait`
