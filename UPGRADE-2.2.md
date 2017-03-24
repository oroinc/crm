UPGRADE FROM 2.1 to 2.2
========================

SalesBundle
-----------
- Class `Oro\Bundle\SalesBundle\Provider\Customer\ConfigProvider`
    - method `getCustomersData` was removed. Use `getGridCustomersData` instead.
- Class `Oro\Bundle\SalesBundle\Controller\CustomerController`
    - action `gridDialogAction` is rendered in `OroDataGridBundle:Grid/dialog:multi.html.twig`
    - action `customerGridAction` was removed
    - method `getCustomersData` was removed
- Class `Oro\Bundle\SalesBundle\Form\Type\CustomerType`
    - construction signature was changed, now it takes the next arguments:
        - `DataTransformerInterface` $transformer
        - `ConfigProvider` $customerConfigProvider
        - `EntityAliasResolver` $entityAliasResolver
        - `CustomerIconProviderInterface` $customerIconProvider
        - `TranslatorInterface` $translator
        - `SecurityFacade` $securityFacade
        - `ManagerInterface` $gridManager
        - `EntityNameResolver` $entityNameResolver
        - `MultiGridProvider` $multiGridProvider

MagentoBundle
-----------
- Classes `Oro\Bundle\MagentoBundle\Form\Extension\CustomerAssociationExtension`, `Oro\Bundle\MagentoBundle\Form\Extension\OpportunityCustomerAssociationExtension`, `Oro\Bundle\MagentoBundle\EventListener\Customer\CustomerAssociationListener` were removed. They are no longer used.
