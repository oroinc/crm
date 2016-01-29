UPGRADE FROM 1.8 to 1.9
=======================

####OroCRMMarketingListBundle:

- Class `OroCRM\Bundle\MarketingListBundle\Model\DataGridConfigurationHelper` moved to `Oro\Bundle\DataGridBundle\Tools\MixinConfigurationHelper`.
- Service `orocrm_marketing_list.datagrid_configuration_helper` moved to `oro_datagrid.mixin_configuration.helper`.
- Constant `OroCRM\Bundle\MarketingListBundle\Datagrid\MarketingListItemsListener::MIXIN` moved to `Oro\Bundle\DataGridBundle\EventListener\MixinListener::GRID_MIXIN`
