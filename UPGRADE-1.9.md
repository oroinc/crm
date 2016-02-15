UPGRADE FROM 1.8 to 1.9
=======================

####OroCRMMarketingListBundle:

- Class `OroCRM\Bundle\MarketingListBundle\Model\DataGridConfigurationHelper` moved to `Oro\Bundle\DataGridBundle\Tools\MixinConfigurationHelper`.
- Service `orocrm_marketing_list.datagrid_configuration_helper` moved to `oro_datagrid.mixin_configuration.helper`.
- Constant `OroCRM\Bundle\MarketingListBundle\Datagrid\MarketingListItemsListener::MIXIN` moved to `Oro\Bundle\DataGridBundle\EventListener\MixinListener::GRID_MIXIN`

####OroCRMAnalyticsBundle:

- Methods definitions of `OroCRM\Bundle\AnalyticsBundle\Builder\AnalyticsBuilderInterface` was updated:
    - `supports($entity)` was changed to `supports(Channel $channel)`,
    - `build(AnalyticsAwareInterface $entity)` was changed to `build(Channel $entity, array $ids = [])`.
- `OroCRM\Bundle\AnalyticsBundle\Builder\RFMProviderInterface` was updated:
    - `supports($entity)` method definition was changed to `supports(Channel $entity)`,
    - `getValue(RFMAwareInterface $entity)` method was removed, 
    - new `getValues(Channel $entity, array $ids = [])` method was added. 
