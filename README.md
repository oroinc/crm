poc-product-entity-design
=========================

POC on product entity design to illustrate attribute management

Based on classic Doctrine 2 classes, entity, repository, entity manager

We add our own SimpleManager, FlexibleManager to be storage agnostic an deals with attribute management.

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

TODO


TODO
====

- behavior : timestamp, soft delete, audit, versionning, translatable / scopable values

- clean way to play with backend type and add some new

- use translatable interface / use a custom translation mecanism in place of doctrine gedmo extension

- think about value representation (should be loaded in product as key/value)

- option with locale code + demo

- sanitize object query results 

- complete product / flexibleentity repository

- should be use an extended Doctrine\ORM\Persisters\ to deal with findBy customization ?

- enhance find($id) to load any values in one query ? (no lazy load when get each value)

- add 10k products with 100 attributes to check the impl
