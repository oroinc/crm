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
- /Model/Behaviour contains interfaces as timestampable, translatable

- /Entity/Mapping contains abstract doctrine entities (with mapping)
- /Entity/Repository contains base doctrine repository for flexible entity
- /Entity contains concrets entities (attribute, option, optionvalue) which can be common for any kind of entity

- /Manager contains service which allow to manipulate, entity, repository and entity manager with simple manager (classic doctrine entity) or flexible manager (attribute management entity)

- /Listener contains event subscriber/listener to implements some behavior as timestampable or translatable

- /Helper contains classes with utility methods

Acme namespace contains some exemples :
- ManufacturerBundle : a simple entity
- CustomerBundle : a flexible entity (no translatable attributes)
- ProductBundle : a flexible entity (with translatable attributes)

Install and run unit tests
==========================

Install as standard symfony 2 app then :
```bash
~/git/poc-product-entity-design$ php app/console doctrine:database:drop --force
~/git/poc-product-entity-design$ php app/console doctrine:database:create 
~/git/poc-product-entity-design$ php app/console doctrine:schema:update --force
```

To run tests :
```bash
~/git/poc-product-entity-design$ php app/console doctrine:database:drop --force --env=test
~/git/poc-product-entity-design$ php app/console doctrine:database:create --env=test
~/git/poc-product-entity-design$ php app/console doctrine:schema:update --force --env=test
~/git/poc-product-entity-design$ phpunit -c app/  --coverage-html=cov/
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

Define the service manager (src/Acme/Bundle/ManufacturerBundle/Resources/config/services.yml) : 
```yaml
parameters:
    manufacturer_manager_class: Oro\Bundle\FlexibleEntityBundle\Manager\SimpleEntityManager
    manufacturer_entity_class:  Acme\Bundle\ManufacturerBundle\Entity\Manufacturer

services:
    manufacturer_manager:
        class:        "%manufacturer_manager_class%"
        arguments:    [@service_container, %manufacturer_entity_class%]
```

How to use :
```php
        // get list
        $mm = $this->container->get('manufacturer_manager');
        $manufacturers = $mm->getEntityRepository()->findAll();
        // create a new one
        $manufacturer = $mm->createEntity();
        $manufacturer->setName('Dell');
        // persist
        $mm->getStorageManager()->persist($manufacturer);
        $mm->getStorageManager()->flush();
        
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

Create a flexible entity (with dynamic attribute management, not translatable)
==============================================================================

Create a customer entity class, extends abstract orm entity which contains basic mapping.

This customer class contains fields mapped at development time, here, email, firstname, lastname.

We use the basic entity repository, and define by mapping which value table to use. 

```php
namespace Oro\Bundle\CustomerBundle\Entity;

use Oro\Bundle\FlexibleEntityBundle\Entity\Mapping\AbstractOrmEntity;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Table(name="acmecustomer_customer")
 * @ORM\Entity(repositoryClass="Oro\Bundle\FlexibleEntityBundle\Entity\Repository\OrmEntityRepository")
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

We define mapping to basic entity attribute, to basic option (for attribute of list type) and to our customer entity.
```php
<?php
namespace Acme\Bundle\CustomerBundle\Entity;

use Oro\Bundle\FlexibleEntityBundle\Entity\Mapping\AbstractOrmEntityAttributeValue;
use Oro\Bundle\FlexibleEntityBundle\Entity\OrmEntityAttribute;
use Doctrine\ORM\Mapping as ORM;
/**

 * @ORM\Table(name="acmecustomer_customer_attribute_value")
 * @ORM\Entity
 */
class CustomerAttributeValue extends AbstractOrmEntityAttributeValue
{
    /**
     * @var Attribute $attribute
     *
     * @ORM\ManyToOne(targetEntity="Oro\Bundle\FlexibleEntityBundle\Entity\OrmEntityAttribute")
     */
    protected $attribute;

    /**
     * @var Entity $entity
     *
     * @ORM\ManyToOne(targetEntity="Customer", inversedBy="values")
     */
    protected $entity;

    /**
     * Store option value, if backend is an option
     *
     * @var AbstractOrmEntityAttributeOption $optionvalue
     *
     * @ORM\ManyToOne(targetEntity="Oro\Bundle\FlexibleEntityBundle\Entity\OrmEntityAttributeOption")
     */
    protected $option;
}
```

Then, we configure our flexible entity in src/Oro/Bundle/CustomerBundle/Resources/config/flexibleentity.yml :
```yaml
entities_config:
    Acme\Bundle\CustomerBundle\Entity\Customer:
        flexible_manager:            customer_manager
        flexible_entity_class:       Acme\Bundle\CustomerBundle\Entity\Customer
        flexible_entity_value_class: Acme\Bundle\CustomerBundle\Entity\CustomerAttributeValue
        # there is some default values added for basic entity to use for attribute, option, etc and for behavior as translatable  
```

This config :
- is validated by Oro\Bundle\FlexibleEntityBundle\DependencyInjection\Configuration
- is loaded / merged with others by Oro\Bundle\FlexibleEntityBundle\DependencyInjection\OroFlexibleEntityExtension
- is accessible as $this->container->getParameter('oro_flexibleentity.entities_config');
- is known by flexible entity manager and repository

Finally we add our service declaration in src/Oro/Bundle/CustomerBundle/Resources/config/services.yml :
```yaml
parameters:
    customer_manager_class: Oro\Bundle\FlexibleEntityBundle\Manager\FlexibleEntityManager
    customer_entity_class:  Acme\Bundle\CustomerBundle\Entity\Customer

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
$att->setTitle('Company');
$att->setBackendModel(AbstractAttributeType::BACKEND_MODEL_ATTRIBUTE_VALUE);
$att->setBackendType(AbstractAttributeType::BACKEND_TYPE_VARCHAR);

// persist and flush
$cm->getStorageManager()->persist($att);
$cm->getStorageManager()->flush();

// create customer with basic fields mapped in customer entity  (cf controllers and unit tests for more exemples with options, etc)
$customer = $cm->createEntity();
$customer->setEmail($custEmail);
$customer->setFirstname('Nicolas');
$customer->setLastname('Dupont');

// get the customer attribute 'company'
$attCompany = $cm->getEntityRepository()->findAttributeByCode('company');

// add a value
$value = $cm->createEntityValue();
$value->setAttribute($attCompany);
$value->setData('Akeneo');
$customer->addValue($value);

// persist and flush
$cm->getStorageManager()->persist($customer);
$cm->getStorageManager()->flush();
```

Create a flexible entity (with dynamic attribute management, translatable)
==========================================================================

Product and ProductValue are defined as for customer.

Flexible manager is define in same way too.

Attribute 'name' and 'description' are defined as translatable (not the case by default) :
```php
$pm = $this->container->get('product_manager');
$attributeCode = 'name';
$attribute = $pm->createAttribute();
$attribute->setCode($attributeCode);
$attribute->setTitle('Description');
$attribute->setBackendModel(AbstractAttributeType::BACKEND_MODEL_ATTRIBUTE_VALUE);
$attribute->setBackendType(AbstractAttributeType::BACKEND_TYPE_TEXT);
$attribute->setTranslatable(true);
```

About locale, if attribute is defined as translatable, the locale to use is retrieved (by priority) :
- from flexible manager if developer has forced it with setLocaleCode($code)
- from http request
- from application config

Base repository is designed to deal with translated values in queries, it knows the asked locale and gets relevant value if attribute is translatable.

Base entity is designed to get values related to asked locale.

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
$this->getProductManager()->setLocaleCode('fr')->getEntityRepository()
    ->findByWithAttributes(array('name', 'description'));

// more examples in controllers an unit tests
```

This method should be extended to add other operators like, in, etc, for now you have to define the method in your custom repository.

How to customize my flexible entity implementation
==================================================

Add some custom queries
-----------------------

- extend OrmEntityRepository in your bundle as :

```php
<?php
class ProductRepository extends OrmFlexibleEntityRepository
```

- write custom queries
- configure custom repository in your flexible entity class as :

```php
<?php
/**
 * Flexible product
 * @ORM\Table(name="acmeproduct_product")
 * @ORM\Entity(repositoryClass="Acme\Bundle\ProductBundle\Entity\Repository\ProductRepository")
 */
class Product extends AbstractOrmEntity
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

Add some custom attribute configuration for a dedicated entity
--------------------------------------------------------------

- create a MyEntityAttribute class with one-one relation to BasicEntityAttribute class
- create your repository to encapsulate the manipulation of basic attribute via myentity attribute

Store attributes, option, option values in custom tables
--------------------------------------------------------

- extend or replace OrmEntityAttribute, OrmEntityAttributeOption, OrmEntityAttributeOptionValue in your bundle
- define the classes to use in our flexibleentity.yml with properties : 'flexible_attribute_class', 'flexible_attribute_option_class', 'flexible_attribute_option_value_class'

Use flat storage for values
---------------------------

- use another backend model for attribute, as flatValues (can define this relation in your flexible entity)
- extends / replace base repository to change queries
- use event / subscriber to change schema on each attribute insert / update / delete

Use document oriented storage for entity/values
-----------------------------------------------

- define your document class and flexible manager
- define manager as other and inject your flexible manager

```yaml
parameters:
    mydoc_manager_class: Acme\Bundle\MyBundle\Manager\MyFlexibleEntityManager
    mydoc_entity_class:  Acme\Bundle\MyBundle\Document\Mydoc

services:
    customer_manager:
        class:     "%mydoc_manager_class%"
        arguments: [@service_container, %mydoc_entity_class%]
```

