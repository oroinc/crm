Flexible entity design
======================

POC on entity design to illustrate attribute management.

Based on classic Doctrine 2 classes, entity, repository, entity manager

Allows to :

- create / use simple entity (no attribute management) as classic doctrine way (entity manager, repository, entity)

- create / use flexible entity (dynamic attribute management by user)

- customize flexible entity (add your own storage, or custom basic classes)

In Oro\Bundle\FlexibleEntityBundle :

- /Model/Entity contains abstract entity models (entity, attribute, value, option, etc) independent of doctrine
- /Model/Attribute (will/should) contains attribute frontend types, backend types, backend models
- /Model/Behavior contains interfaces as timestampable, translatable, hasrequiredvalue, hasdefaultvalue

- /Entity/Mapping contains abstract doctrine entities (with mapping)
- /Entity/Repository contains base doctrine repository for flexible entity
- /Entity contains concrets entities (attribute, option, optionvalue) which can be common for any kind of entity

- /Manager contains service which allow to manipulate, entity, repository and entity manager with simple manager (classic doctrine entity) or flexible manager (attribute management entity)

- /Listener contains event subscriber/listener to implements some behavior as timestampable, translatable, etc

- /Helper contains classes with utility methods

There are some examples in laboro/bap-standard/tree/master/src/Acme/Bundle/DemoFlexibleEntityBundle :
- Manufacturer : a simple entity
- Customer : a flexible entity (no translatable attributes)
- Product : a flexible entity (with translatable and scopable attributes)

Install
=======

To install for dev :

```bash
$ php composer.phar update --dev
```
To use as dependency, use composer and add bundle in your AppKernel :

```yaml
    "require": {
        [...]
        "oro/FlexibleEntityBundle": "dev-master"
    },
    "repositories": [
        {
            "type": "vcs",
            "url": "git@github.com:laboro/FlexibleEntityBundle.git",
            "branch": "master"
        }
    ]

```

Run unit tests
==============

```bash
$ phpunit --coverage-html=cov/
```

Create a simple entity (no attribute management)
================================================

Create an entity class (classic doctrine way) :
```php
use Doctrine\ORM\Mapping as ORM;

/**
 * Manufacturer entity
 *
 * @ORM\Table(name="acmemanufacturer_manufacturer")
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

Define the service manager (src/Acme/Bundle/DemoFlexibleEntityBundle/Resources/config/services.yml) : 
```yaml
parameters:
    manufacturer_manager_class: Oro\Bundle\FlexibleEntityBundle\Manager\SimpleManager
    manufacturer_entity_class:  Acme\Bundle\DemoFlexibleEntityBundle\Entity\Manufacturer

services:
    manufacturer_manager:
        class:     "%manufacturer_manager_class%"
        arguments: [@service_container, %manufacturer_entity_class%]
```

How to use :
```php
        // get list
        $manager = $this->container->get('manufacturer_manager');
        $manufacturers = $manager->getEntityRepository()->findAll();
        // create a new one
        $manufacturer = $manager->createFlexible();
        $manufacturer->setName('Dell');
        // persist
        $manager->getStorageManager()->persist($manufacturer);
        $manager->getStorageManager()->flush();
        
```

In this case, we can directly use classic way too with :
```php
        // get list
        $em = $this->container->get('doctrine.orm.entity_manager');
        $manufacturers = $em->getRepository('AcmeManufacturerBundle:Manufacturer')->findAll();
        // create a new one
        $manufacturer = new Manufacturer();
        $manufacturer->setName('Dell');
        // persist
        $em->persist($manufacturer);
        $em->flush();
```

Create a flexible entity (with dynamic attribute management)
============================================================

Create a customer entity class, extends abstract orm entity which contains basic mapping.

This customer class contains fields mapped at development time, here, email, firstname, lastname.

We use the basic entity repository, and define by mapping which value table to use. 

```php
namespace Acme\Bundle\DemoFlexibleEntityBundle\Entity;

use Oro\Bundle\FlexibleEntityBundle\Entity\Mapping\AbstractEntityFlexible;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Table(name="acmecustomer_customer")
 * @ORM\Entity(repositoryClass="Oro\Bundle\FlexibleEntityBundle\Entity\Repository\FlexibleEntityRepository")
 */
class Customer extends AbstractEntityFlexible
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
     * @ORM\OneToMany(targetEntity="CustomerValue", mappedBy="entity", cascade={"persist", "remove"})
     */
    protected $values;

    // ... getter / setter
```

Then we have to define customer attribute value entity, extends basic one which contains mapping.

We define mapping to basic entity attribute, to basic option (for attribute of list type) and to our customer entity.
```php
<?php
namespace Acme\Bundle\DemoFlexibleEntityBundle\Entity;

use Oro\Bundle\FlexibleEntityBundle\Entity\Mapping\AbstractEntityFlexibleValue;
use Oro\Bundle\FlexibleEntityBundle\Entity\Attribute;
use Doctrine\ORM\Mapping as ORM;
/**

 * @ORM\Table(name="acmecustomer_customer_attribute_value")
 * @ORM\Entity
 */
class CustomerValue extends AbstractEntityFlexibleValue
{
    /**
     * @var Attribute $attribute
     *
     * @ORM\ManyToOne(targetEntity="Oro\Bundle\FlexibleEntityBundle\Entity\Attribute")
     */
    protected $attribute;

    /**
     * @var Entity $entity
     *
     * @ORM\ManyToOne(targetEntity="Customer", inversedBy="values")
     */
    protected $entity;

    /**
     * Custom backend type to store options and theirs values
     *
     * @var options ArrayCollection
     *
     * @ORM\ManyToMany(targetEntity="Oro\Bundle\FlexibleEntityBundle\Entity\AttributeOption")
     * @ORM\JoinTable(name="acmedemoflexibleentity_customer_values_options",
     *      joinColumns={@ORM\JoinColumn(name="value_id", referencedColumnName="id", onDelete="CASCADE")},
     *      inverseJoinColumns={@ORM\JoinColumn(name="option_id", referencedColumnName="id", onDelete="CASCADE")}
     * )
     */
    protected $options;
}
```

Then, we configure our flexible entity in src/Acme/Bundle/DemoFlexibleEntityBundle/Resources/config/flexibleentity.yml :
```yaml
entities_config:
    Acme\Bundle\DemoFlexibleEntityBundle\Entity\Customer:
        flexible_manager:            customer_manager
        flexible_class:       Acme\Bundle\DemoFlexibleEntityBundle\Entity\Customer
        flexible_value_class: Acme\Bundle\DemoFlexibleEntityBundle\Entity\CustomerValue
        # there is some default values added for basic entity to use for attribute, option, etc 
```

This config :
- is validated by Oro\Bundle\FlexibleEntityBundle\DependencyInjection\Configuration
- is loaded / merged with others by Oro\Bundle\FlexibleEntityBundle\DependencyInjection\OroFlexibleEntityExtension
- is accessible as $this->container->getParameter('oro_flexibleentity.entities_config');
- is known by flexible entity manager and repository

Finally we add our service declaration in src/Acme/Bundle/DemoFlexibleEntityBundle/Resources/config/services.yml :
```yaml
parameters:
    customer_manager_class: Oro\Bundle\FlexibleEntityBundle\Manager\FlexibleManager
    customer_entity_class:  Acme\Bundle\DemoFlexibleEntityBundle\Entity\Customer

services:
    customer_manager:
        class:     "%customer_manager_class%"
        arguments: [@service_container, %customer_entity_class%]
```

How to use :
```php
// get customer manager
$cm = $this->container->get('customer_manager');

// create an attribute (cf controllers and unit tests for more exemples with options, etc)
$att = $cm->createAttribute();
$att->setCode($attCode);
$att->setBackendType(AbstractAttributeType::BACKEND_TYPE_VARCHAR);

// persist and flush
$cm->getStorageManager()->persist($att);
$cm->getStorageManager()->flush();

// create customer with basic fields mapped in customer entity  (cf controllers and unit tests for more exemples)
$customer = $cm->createFlexible();
$customer->setEmail($custEmail);
$customer->setFirstname('Nicolas');
$customer->setLastname('Dupont');

// get the customer attribute 'company'
$attCompany = $cm->getEntityRepository()->findAttributeByCode('company');

// add a value
$value = $cm->createFlexibleValue();
$value->setAttribute($attCompany);
$value->setData('Akeneo');
$customer->addValue($value);

// persist and flush
$cm->getStorageManager()->persist($customer);
$cm->getStorageManager()->flush();
```

Define translated values
========================

A value can be translated if related attribute is defined as translatable.

By default, attribute is defined as not translatable, you have to setup as following :

```php
$pm = $this->container->get('product_manager');
$attributeCode = 'name';
$attribute = $pm->createAttribute();
$attribute->setCode($attributeCode);
$attribute->setBackendType(AbstractAttributeType::BACKEND_TYPE_TEXT);
$attribute->setTranslatable(true);
```

You can choose value locale as following and use any locale code you want (fr, fr_FR, other, no checks, depends on application, list of locales is available in Locale Component) :

```php
$value = $pm->createFlexibleValue();
$value->setAttribute($attribute);
$value->setData('my data');
// force locale to use
$value->setLocale('fr_FR');
```

If you don't choose locale of value, it's created with locale code (high to low priority) :
- of flexible entity manager
- of flexible entity config (see default_locale)

Base flexible entity repository is designed to deal with translated values in queries, it knows the asked locale and gets relevant value if attribute is translatable.

Base flexible entity is designed to gets relevant values too, it knows the asked locale (injected with TranslatableListener).

Define a value with a scope
===========================

A value can also be scoped if related attribute is defined as scopable.

By default, attribute is defined as not scopable, you have to setup as following :

```php
$pm = $this->container->get('product_manager');
$attributeCode = 'description';
$attribute = $pm->createAttribute();
$attribute->setCode($attributeCode);
$attribute->setBackendType(AbstractAttributeType::BACKEND_TYPE_TEXT);
$attribute->setTranslatable(true);
$attribute->setScopable(true);
```

Then you can use any scope code you want for value (no checks, depends on application).

```php
$pm = $this->container->get('product_manager');
$value = $pm->createFlexibleValue();
$value->setScope('my_scope_code');
$value->setAttribute($attDescription);
$value->setData('my scoped and translated value');
```

If you want associate a default scope to any created value, define it in config file with "default_scope" param.

Base flexible entity repository is designed to deal with scoped values in queries, it knows the asked scope and gets relevant value if attribute is scopable.

Base flexible entity is designed to gets relevant values too, it knows the asked scopable (injected with ScopableListener).

Define a value with a currency
==============================

A value can be related to a currency.

You can use any currency code you want (no checks, depends on application, list of currencies is available in Locale Component).

```php
$pm = $this->container->get('product_manager');
$value = $pm->createFlexibleValue();
$value->setAttribute($attPrice);
$value->setData(100);
$value->setCurrency('EURO');
```

Define a value with a unit
==========================

A value can be related to a measure unit.

You can use any unit code you want (no checks, depends on application).

```php
$pm = $this->container->get('product_manager');
$value = $pm->createFlexibleValue();
$value->setAttribute($attPrice);
$value->setData(100);
$value->setUnit('cm');
```

Add some attribute configuration for a dedicated entity in a custom table
=========================================================================

- for instance, create a ProductAttribute class with one-one relation to base Attribute class and add some custom attribute fields, as Name, Description, etc :

```php
<?php
namespace Acme\Bundle\DemoFlexibleEntityBundle\Entity;

use Oro\Bundle\FlexibleEntityBundle\Entity\Mapping\AbstractEntityAttributeExtended;
use Oro\Bundle\FlexibleEntityBundle\Entity\Attribute;
use Doctrine\ORM\Mapping as ORM;

/**
 * Custom properties for a product attribute
 *
 * @ORM\Table(name="acmeproduct_product_attribute")
 * @ORM\Entity
 */
class ProductAttribute extends AbstractEntityAttributeExtended 
{
    /**
     * @var Oro\Bundle\FlexibleEntityBundle\Entity\Attribute $attribute
     *
     * @ORM\OneToOne(targetEntity="Oro\Bundle\FlexibleEntityBundle\Entity\Attribute", cascade={"persist", "merge", "remove"})
     * @ORM\JoinColumn(name="attribute_id", referencedColumnName="id", onDelete="cascade")
     */
    protected $attribute;

    /**
     * @var string $name
     *
     * @ORM\Column(name="name", type="string", length=255)
     */
    protected $name;

    /**
     * @var string $description
     *
     * @ORM\Column(name="description", type="string", length=255)
     */
    protected $description;

    /**
     * @ORM\Column(name="is_smart", type="boolean")
     */
    protected $smart;

    /**
     * Get name
     * @return string $name
     */
    public function getName()
    {
        return $this->name;
    }
    // [...]
}
```

- add attribute_extended_class in config :

```yaml
entities_config:
    Acme\Bundle\DemoFlexibleEntityBundle\Entity\Product:
        flexible_manager:                  product_manager
        flexible_class:             Acme\Bundle\DemoFlexibleEntityBundle\Entity\Product
        flexible_value_class:       Acme\Bundle\DemoFlexibleEntityBundle\Entity\ProductValue
        attribute_extended_class: Acme\Bundle\DemoFlexibleEntityBundle\Entity\ProductAttribute
```

- then you can create / manipulate some custom attribute as following :

```php
// create product attribute
$productAttribute = $this->getProductManager()->createAttributeExtended();
$productAttribute->setName('Name');
$productAttribute->setCode($attributeCode);
$productAttribute->setRequired(true);
$productAttribute->setBackendType(AbstractAttributeType::BACKEND_TYPE_VARCHAR);
$productAttribute->setTranslatable(true);
$this->getProductManager()->getStorageManager()->persist($productAttribute);

// to query on product attributes :
$this->getProductManager()->getAttributeExtendedRepository();
```

Note that product attribute mapping provides cascades to create / delet related base attribute too.

AbstractEntityAttributeExtended provides equaly some shortcuts to base attribute accessors (required, unique, etc) to directly manipulate custom attribute.

About queries on flexible entity
================================

We can use classic findBy() method of repository to retrieve entity collection (native Symfony shortcurt to build doctrine query)

```php
// get only entities, values and attributes are lazy loaded, you can use any criteria, order, limit 
$products = $this->getProductManager()->getEntityRepository()->findBy(array());

```
We have added a findByWithAttributes() in flexible repository which have the same signature, just attribute codes to select as first param.

This method cover the same features than findBy, add basic criterias, order by, limit on field or attribute.

```php
$productRepository = $this->getProductManager()->getEntityRepository();
// get all entity fields and values (no lazy loading)
$products = $productRepository->findByWithAttributes();
// select few attributes
$products = $productRepository->findByWithAttributes(array('name'));
// filter on field and attribute values
$products = $productRepository->findByWithAttributes(array(), array('sku' => 'sku-2'));
$products = $productRepository->findByWithAttributes(array('description', 'size'), array('size' => 175));
// use order 
$products = $productRepository->findByWithAttributes(
    array('name', 'description'), null, array('description' => 'desc', 'id' => 'asc')
);
// use limit 
$products = $productRepository->findByWithAttributes(array('name', 'description'), null, null, 10, 0);
// force locale to get french values
$this->getProductManager()->setLocale('fr_FR')->getEntityRepository()
    ->findByWithAttributes(array('name', 'description'));

// more examples in controllers an unit tests
```

This method should be extended to add other operators like, in, etc, for now you have to define the method in your custom repository.

Another interesting method is prepareQueryBuilder(), it allows to prepare query builder with attribute to select, criterias, order.

As it returns a QueryBuilder you can get the query add some very custom clauses, add lock mode, change hydration mode, etc. 

There is also a method to load a flexible entity and all values without lazy loading : 

```php
// to load one flexible entity with lazy loading, classic way
$customer = $this->getCustomerManager()->getEntityRepository()->find($id);

// with all values not lazy loaded with new method
$customer = $this->getCustomerManager()->getEntityRepository()->findWithAttributes($id);
```

Use a non default entity manager
================================

Used entity manager is the default one "doctrine.orm.entity_manager" (cf SimpleManager)

If you want to use another one, you can define it with optional parameter as following :

```yaml
services:
    customer_manager:
        class:     "%customer_manager_class%"
        arguments: [@service_container, %customer_entity_class%, @doctrine.orm.non_default_entity_manager]
```

Others customizations on flexible entity implementation
=======================================================

Add some custom queries
-----------------------

- extend FlexibleEntityRepository in your bundle as :

```php
<?php
class ProductRepository extends lexibleEntityRepository
```

- write custom queries
- configure custom repository in your flexible entity class as :

```php
<?php
/**
 * Flexible product
 * @ORM\Table(name="acmeproduct_product")
 * @ORM\Entity(repositoryClass="Acme\Bundle\DemoFlexibleEntityBundle\Entity\Repository\ProductRepository")
 */
class Product extends AbstractEntityFlexible
{
```

Add some behavior related to flexible (as audit, log)
-----------------------------------------------------

- use event / subscriber to plug custom code (as for translatable behavior, see TranslatableListener)
- if needed, you can retrieve relevant flexible entity manager from entity full qualified class name as :
 
```php
<?php
// get flexible config and manager
$flexibleConfig = $this->container->getParameter('oro_flexibleentity.entities_config');
$flexibleManagerName = $flexibleConfig['entities_config'][$flexibleEntityClass]['flexible_manager'];
$flexibleManager = $this->container->get($flexibleManagerName);
```

Store attributes, option, option values in custom tables
--------------------------------------------------------

- extend or replace Attribute, AttributeOption, AttributeOptionValue in your bundle
- define the classes to use in our flexibleentity.yml with properties : 'attribute_class', 'flexible_attribute_option_class', 'attribute_option_value_class'

Use flat storage for values
---------------------------

- use another backend storage for attribute, as flatValues (can define this relation in your flexible entity)
- extends / replace base repository to change queries
- use event / subscriber to change schema on each attribute insert / update / delete

Use document oriented storage for entity/values
-----------------------------------------------

- define your document class and flexible manager in your bundle
- define flexible document manager as basic one and inject it in your flexible service manager as :

```yaml
parameters:
    mydoc_manager_class: Acme\Bundle\MyBundle\Manager\MyFlexibleManager
    mydoc_entity_class:  Acme\Bundle\MyBundle\Document\MyDocument

services:
    customer_manager:
        class:     "%mydoc_manager_class%"
        arguments: [@service_container, %mydoc_entity_class%]
```

TODO
====

- add unit tests on hasrequiredvalue listener

- add is_unique behavior

ENHANCEMENT
===========

- use event dispatcher in flexible manager on createAttribute, createEntity, etc to allow to plug some code 

- deal with in, like, etc in queries 

- default fallback (locale, scope) in queries
