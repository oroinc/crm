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

About "locale scope", each attribute can be defined as translatable, in this case the default rule to get the locale to use is by priority :
- from flexible manager if developer has forced it
- from http request
- from application config

Install and unit tests
======================

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
        $manufacturer = $mm->getNewEntityInstance();
        $manufacturer->setName('Dell');
        // persist
        $mm->getStorageManager()->persist($manufacturer);
        $mm->getStorageManager()->flush();
        
```

In this case, we can directly use classic way too with :
```php
        // get list
        $em = $this->container->get('doctrine.orm.entity_manager');
        $manufacturers = $em->getRepository('AcmeBundle:Manufacturer')->findAll();
        // create a new one
        $manufacturer = new Manufacturer();
        $manufacturer->setName('Dell');
        // persist
        $em->persist($manufacturer);
        $em->flush();
        
```

Create a flexible entity (with attribute management)
====================================================

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
$att->setTitle('Company');
$att->setBackendModel(AbstractAttributeType::BACKEND_MODEL_ATTRIBUTE_VALUE);
$att->setBackendType(AbstractAttributeType::BACKEND_TYPE_VARCHAR);

// persist and flush
$cm->getStorageManager()->persist($att);
$cm->getStorageManager()->flush();

// create customer with basic fields mapped in customer entity  (cf controllers and unit tests for more exemples with options, etc)
$customer = $cm->getNewEntityInstance();
$customer->setEmail($custEmail);
$customer->setFirstname('Nicolas');
$customer->setLastname('Dupont');

// get the customer attribute 'company'
$attCompany = $cm->getAttributeRepository()->findOneByCode('company');

// add a value
$value = $cm->createEntityValue();
$value->setAttribute($attCompany);
$value->setData('Akeneo');
$customer->addValue($value);

// persist and flush
$cm->getStorageManager()->persist($customer);
$cm->getStorageManager()->flush();
```





TODO : refactor following
Customize my flexible entity implementation
===========================================

- extend OrmEntityRepository and define it in your flexible entity class to add some complex / specific queries, your own scope rules, other business rules

- extend FlexibleEntityManager and define it's use in services.yml file to add some entity behaviour (as versionning), shortcut methods, other storage

- use event / listener to plug some custom code (as for translatable behaviour)

- extend OrmEntityAttribute, OrmEntityAttributeOption, OrmEntityAttributeOptionValue to store your attributes, etc in custom tables (not with other entities)
```yaml
# to use another attribute entity
parameters:
    product_manager.class:          Oro\Bundle\FlexibleEntityBundle\Manager\FlexibleEntityManager
    product_entity.shortname:       OroProductBundle:Product
    product_value.shortname:        OroProductBundle:ProductAttributeValue
    # add following lines
    product_attribute.shortname:    OroProductBundle:ProductAttribute
    product_option.shortname:       OroProductBundle:ProductAttributeOption
    product_option_value.shortname: OroProductBundle:ProductAttributeOptionValue

services:
    product_manager:
        class:        "%product_manager.class%"
        arguments:    [@service_container, %product_entity.shortname%, , %product_value.shortname%]
        # add following calls to use your own impl
        calls:
                    - [ setAttributeShortname, [ %product_attribute.shortname% ] ]
                    - [ setAttributeOptionShortname, [ %product_option.shortname% ] ]
                    - [ setAttributeOptionValueShortname, [ %product_option_value.shortname% ] ]


TODO
====

- implements is_required attribute behavior by using event / subscriber (prePersist, preUpdate an entity)

- implements is_unique attribute behavior by using event / subscriber (prePersist, preUpdate a value)

- add a configuration json field in attribute entity (to store custom configuration, for now)

- fix bug with null value for date and datetime because set up with now()

Others topics
=============

is_scopable :
- product value can have a scope
- add an interface setScope(string $scope), getScope()
- product entity implements interface
- change getValue / setValue when scope is defined for entity
- save / get in locale + scope

prepare product data set

Flexible Entity
- add shortcut to get entity option and entity option value ?
- enhance find($id) to load any values in one query ? (no lazy load when get each value), play with doctrine cascade ?
- provide shortcut to setData / updateData 

Entity/Mapping/
- AbstractOrmEntityEAV    @attributeValues
- AbstractOrmEntityFlat   @flatValues
- AbstractOrmEntityHybrid @attributeValues + @flatValues
- AbstractOrmEntityAttributeValue
- AbstractOrmEntityFlatValue

Flexible entity repository
- direct join on option ?

Translatable behavior
- can be optionnal for flexible ?

Attribute
- how to extends to add some custom conf (for instance scope for product ?)

Test
- add 10k products with 100 attributes to check the impl
