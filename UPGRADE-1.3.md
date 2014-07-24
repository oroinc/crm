UPGRADE FROM 1.2 to 1.3
=======================

### General

* Campaign bundle was added
* Case bundle was added
* Contact bundle changed:
	* Removed `GroupNormalizer`, `MethodNormalizer`, `SourceNormalizer`, `AddOrReplaceStrategy`, `ContactImportStrategyHelper` - removed as it is not needed.
* Account bundle change:
	* Remove `AccountNormalizer` as it is not needed
* Call bundle changed:
	* Remove `view_content_data_communications` placeholder use.
* Contact us bundle changed:
	* Removed `ChannelRelatedDataDeleteProvider` as it is not needed
* Magento bundle changed:
	* Added Cart select form type `orocrm_cart_select`
	* Added Customer select form type `orocrm_customer_select`
	* Added Order select form type `orocrm_order_select`
	* Renamed `MagentoChannelDeleteProvider` -> `MagentoDeleteProvider`
	* Add `ImportHelper` which provide functionality to get channel from import export context