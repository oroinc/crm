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
 * Supplier entity
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
        arguments:    [@doctrine.orm.entity_manager, %manufacturer_entity.shortname%]
```

Use :
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

TODO


TODO
====

- clean way to play with backend type and add some new

- use many tables for values ?

- use translatable interface / use a custom translation mecanism in place of doctrine gedmo extension

- use a distinct attribute manager (not mixed with product manager)

- think about value representation (should be loaded in product as key/value)

- demo on options usage

- sanitize object query results 

- complete product / flexibleentity repository

- should be use an extended Doctrine\ORM\Persisters\ to deal with findBy customization ?

- enhance find($id) to load any values in one query ? (no lazy load when get each value)

- add 10k products with 100 attributes to check the impl
