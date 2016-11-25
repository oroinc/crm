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
This option allow configure this relation and display special form type to choose or create customer from any allowed types of customer in application.
You can define customer icon image, choose grid and create form route data.
Customer info will shown on opportunity, lead view page and on entity edit page.


Migration Extension
-------------------

For enable association you should create migration and add relation to opportunity and lead. 
For this we have two migration extensions - OpportunityExtension, LeadExtension.

Migration example:

```
class YourMigration implements Migration, OpportunityExtensionAwareInterface, LeadExtensionAwareInterface
{
    use LeadExtensionTrait, OpportunityExtensionTrait;

    /**
     * {@inheritdoc}
     */
    public function up(Schema $schema, QueryBag $queries)
    {
        $this->leadExtension->addCustomerAssociation($schema, 'target_customer_table');
        $this->opportunityExtension->addCustomerAssociation($schema, 'target_customer_table');
    }
}
```


Icon Provider
--------------

You can define customer icon image for your associated customer entity. This image will appear in list of customers in form type component.
For define this icon you should implement CustomerIconProviderInterface and register it class as service.

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
```
