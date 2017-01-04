Sales Customer Relations
========================


Table of Contents
-----------------
 - [Sales Entities Account associations](#sales-entities-account-associations)
 - [Migration Extension](#migration-extension)
 - [Icon Provider](#icon-provider)
 - [Entity Configuration](#entity-configuration)


Sales Entities Account Associations
-----------------------------------

As developer you have possibility to create association to sales lead and opportunity entities with some own customer entity.
This option allows to configure this relation and to display special form type to choose or create customer from any allowed types of customer in application.
You can define icon image, choose grid and create form route data.
Customer info will show on opportunity, lead view page and on entity edit page.


Migration Extension
-------------------

To enable association you should create migration and add relation to opportunity and lead. 
For this we have migration extension - CustomerExtension.

Migration example:

```
class YourMigration implements Migration, CustomerExtensionAwareInterface
{
    use CustomerExtensionTrait;

    /**
     * {@inheritdoc}
     */
    public function up(Schema $schema, QueryBag $queries)
    {
        $this->customerExtension->addCustomerAssociation($schema, 'target_customer_table');
    }
}
```


Icon Provider
--------------

You can define icon image for your associated customer entity. This image will appear in list of customers in form type component.
To define this icon you should create class which implements CustomerIconProviderInterface and register the class as service with 
tag oro_sales.customer_icon.

Example:

```
class CustomerIconProvider implements CustomerIconProviderInterface
{
    const CUSTOMER_ICON_FILE = 'bundles/yourbundlename/img/customer-logo.png';

    /**
     * {@inheritdoc}
     */
    public function getIcon($entity)
    {
        if (!$entity instanceof Customer) {
            return null;
        }

        return new Image(Image::TYPE_FILE_PATH, ['path' => '/' . self::CUSTOMER_ICON_FILE]);
    }
}
```

service.yml file:

```
    oro_sales.provider.customer.customer_icon:
        class: Oro\Bundle\YourBundle\Provider\Customer\CustomerIconProvider
        tags:
            - { name: oro_sales.customer_icon }
```


Entity Configuration
--------------------

For correct work customer integration function we should configure some option for customer entity.
First we should to add default grid configuration.
If you want to create option to enable for type component you should to add routeCreate option to config

Example:

```
/**
 * @ORM\Entity()
 * @ORM\Table(name="your_customer")
 *
 * @Config(
 *      routeCreate="your_customer_entity_create",
 *      defaultValues={
 *          "grid"={
 *              "default"="your-customer-select-grid"
 *          },
...
 *      }
 * )
 */
class Customer extends ExtendCustomer
...
```
