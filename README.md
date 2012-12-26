Entity design
=============

POC on entity design to illustrate attribute management.

Based on classic Doctrine 2 classes, entity, repository, entity manager

Allows to :

- create / use simple entity (no attribute management)

- create / use flexible entity (attribute management)

- customize flexible entity (add your own storage, or custom basic classes)

We add our own SimpleManager and FlexibleManager to be storage agnostic and deals with attribute management.

In Oro\Bundle\DataModelBundle :

- folder Model contains base entities (entity, attribute, value, option, etc) independent of doctrine

- folder Entity contains abstract doctrine entities (as AbstractOrmEntity, etc) with base doctrine mapping, and contains base concret entity too (as OrmEntityAttribute, etc) which can be use by any kind of entity

About "locale scope", each attribute can be defined as translatable, then, for queries or create/update, the locale is retrieved as following :

```php
    // from http request as app_dev.php/en/customer/customer/view/2
    $this->localeCode = $this->container->get('request')->getLocale();
    // if not defined it used the default define in application parameters.yml
    if (!$this->localeCode) {
        $this->localeCode = $this->container->parameters['locale'];
    }
```

You can force by using $manager->setLocaleCode($myCode);

Create a simple entity (no attribute management)
================================================

Create an entity class (classic doctrine way) :
```php
use Doctrine\ORM\Mapping as ORM;

/**
 * Manufacturer entity
 *
 * @ORM\Table(name="manufacturer")
 * @ORM\Entity()
 */
class Manufacturer
{
    /**
     * @var integer $id
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @var string $name
     *
     * @ORM\Column(name="name", type="string", length=255)
     */
    private $name;

    /**
     * Get id
     *
     * @return integer
     */
    public function getId()
    {
        return $this->id;
    }
    
    // ... getter / setter
```

Define the service manager (src/Oro/Bundle/ManufacturerBundle/Resources/config/services.yml) : 
```yaml
parameters:
    manufacturer_manager.class:       Oro\Bundle\DataModelBundle\Service\SimpleEntityManager
    manufacturer_entity.shortname:    OroManufacturerBundle:Manufacturer

services:
    manufacturer_manager:
        class:        "%manufacturer_manager.class%"
        arguments:    [@service_container, %manufacturer_entity.shortname%]
```

How to use :
```php
        // get list
        $mm = $this->container->get('manufacturer_manager');
        $manufacturers = $mm->getEntityRepository()->findAll();
        // create a new one
        $manufacturer = $mm->getNewEntityInstance();
        $manufacturer->setName('Dell');
        // persist
        $mm->getStorageManager()->persist($manufacturer);
        $mm->getStorageManager()->flush();
        
```

Create a flexible entity (with attribute management)
====================================================

Create a customer entity class, extends abstract orm entity which contains basic mapping.

We use the basic entity repository and has to define which value table is used. 

```php
<?php
namespace Oro\Bundle\CustomerBundle\Entity;

use Oro\Bundle\DataModelBundle\Entity\AbstractOrmEntity;
use Doctrine\ORM\Mapping as ORM;

/**
 * Flexible customer entity
 *
 * @ORM\Table(name="customer_entity")
 * @ORM\Entity(repositoryClass="Oro\Bundle\DataModelBundle\Entity\OrmEntityRepository")
 */
class Customer extends AbstractOrmEntity
{
    /**
     * @var string $email
     *
     * @ORM\Column(name="email", type="string", length=255, unique=true)
     */
    protected $email;

    /**
     * @var string $firstname
     *
     * @ORM\Column(name="firstname", type="string", length=255)
     */
    protected $firstname;

    /**
     * @var string $lastname
     *
     * @ORM\Column(name="lastname", type="string", length=255)
     */
    protected $lastname;

    /**
     * @var Value
     *
     * @ORM\OneToMany(targetEntity="CustomerAttributeValue", mappedBy="entity", cascade={"persist", "remove"})
     */
    protected $values;

    // ... getter / setter
```

Then we have to define customer attribute value entity, extends basic one which contains mapping.

We define mapping to basic entity attribute and to defined customer entity.
```php
namespace Oro\Bundle\CustomerBundle\Entity;
use Oro\Bundle\DataModelBundle\Model\AbstractOrmEntity;
use Oro\Bundle\DataModelBundle\Entity\AbstractOrmEntityAttributeValue;
use Oro\Bundle\DataModelBundle\Entity\OrmEntityAttribute;
/**
 * Value for a customer attribute
 *
 * @ORM\Table(name="customer_attribute_value")
 * @ORM\Entity
 */
class CustomerAttributeValue extends AbstractOrmEntityAttributeValue
{
    /**
     * @var Attribute $attribute
     *
     * @ORM\ManyToOne(targetEntity="Oro\Bundle\DataModelBundle\Entity\OrmEntityAttribute")
     */
    protected $attribute;

    /**
     * @var Entity $entity
     *
     * @ORM\ManyToOne(targetEntity="Customer", inversedBy="values")
     */
    protected $entity;

}
```

Finally we add service declaration in src/Oro/Bundle/CustomerBundle/Resources/config/services.yml :
```yaml
parameters:
    customer_manager.class:       Oro\Bundle\DataModelBundle\Service\FlexibleEntityManager
    customer_entity.shortname:    OroCustomerBundle:Customer
    customer_value.shortname:     OroCustomerBundle:CustomerAttributeValue

services:
    customer_manager:
        class:        "%customer_manager.class%"
        arguments:    [@service_container, %customer_entity.shortname%, %customer_value.shortname%]
```

How to use :
```php
// get customer manager
$cm = $this->container->get('customer_manager');

// create an attribute (cf AttributeController for more exemples)
$att = $cm->getNewAttributeInstance();
$att->setCode($attCode);
$att->setTitle('Company');
$att->setType(AbstractEntityAttribute::TYPE_STRING);
$att->setTranslatable(false);

// persist and flush
$cm->getStorageManager()->persist($att);
$cm->getStorageManager()->flush();

// create customer with basic fields mapped in customer entity (cf ProductController for more exemples)
$customer = $cm->getNewEntityInstance();
$customer->setEmail($custEmail);
$customer->setFirstname('Nicolas');
$customer->setLastname('Dupont');

// get the customer attribute 'company'
$attCompany = $cm->getAttributeRepository()->findOneByCode('company');

// add a value
$value =$cm->getNewAttributeValueInstance();
$value->setAttribute($attCompany);
$value->setData('Akeneo');
$customer->addValue($value);

// persist and flush
$cm->getStorageManager()->persist($customer);
$cm->getStorageManager()->flush();
```

Customize my flexible entity implementation
===========================================

- extends OrmEntityRepository and define it in my entity classes to add some complex / specific queries, your own scope rules, other business rules

- extends FlexibleEntityManager and define it's use in services.yml file to add some entity behaviour (as versionning) or shortcut methods

- extends OrmEntityAttribute or OrmEntityOption, etc to store your attributes, or options in a custom table (no with other entities)

- use event / listener to plug some custom code


TODO
====

- behavior : timestamp, soft delete, audit, versionning, translatable / scopable values

- clean way to play with backend type and add some new

- use translatable interface / use a custom translation mecanism in place of doctrine gedmo extension

- think about value representation (should be loaded in product as key/value)

- add shortcut to get entity value, data, datatext (option value)

- flexibleentity repository

- enhance find($id) to load any values in one query ? (no lazy load when get each value)

- clean Abstract (model and entity) classes to refactor some methods (addValue, addOption, etc related to owner side)

- add 10k products with 100 attributes to check the impl
