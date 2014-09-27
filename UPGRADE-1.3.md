UPGRADE FROM 1.2 to 1.3
=======================

### General notes

* Campaign bundle has been added
* Case bundle has been added
* Contact bundle has been changed:
	*	`GroupNormalizer`, `MethodNormalizer`, `SourceNormalizer`, `AddOrReplaceStrategy`, `ContactImportStrategyHelper` have been removed as they are no longer needed.
* Account bundle has been changed:
	*	`AccountNormalizer` has been removed as it is no longer needed
* Call bundle has been changed:
	*	`view_content_data_communications` placeholder use has been removed.
* Contact us bundle has been changed:
	*	Removed `ChannelRelatedDataDeleteProvider` as it is not needed.
*	Magento bundle has been changed:
	*	`orocrm_cart_select` cart select form type has been added.
	*	`orocrm_customer_select` customer select form type has been added.
	*	`orocrm_order_select` order select form type has been added.
	*	`MagentoChannelDeleteProvider` has been renamed to `MagentoDeleteProvider`.
	*	`ImportHelper` that provides functionality to get channel from import export context has been added