Extended attribute
==================

In some case, we want add some custom attribute properties / configuration to base attribute in application scope.

For instance, we want to store name and description for any product attribute.

```php
<?php
namespace Acme\Bundle\DemoBundle\Entity;

use Oro\Bundle\FlexibleEntityBundle\Entity\Mapping\AbstractEntityAttributeExtended;
use Oro\Bundle\FlexibleEntityBundle\Entity\Attribute;
use Doctrine\ORM\Mapping as ORM;

/**
 * Custom properties for a product attribute
 *
 * @ORM\Table(name="acme_product_attribute")
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
    Acme\Bundle\DemoBundle\Entity\Product:
        flexible_manager:         product_manager
        flexible_class:           Acme\Bundle\DemoBundle\Entity\Product
        flexible_value_class:     Acme\Bundle\DemoBundle\Entity\ProductValue
        attribute_extended_class: Acme\Bundle\DemoBundle\Entity\ProductAttribute
```

- then you can create / manipulate some custom attribute as following :

```php
// create product attribute
$productAttribute = $this->getProductManager()->createAttributeExtended(new TextType());
$productAttribute->setName('Name');
$productAttribute->setCode($attributeCode);
$productAttribute->setRequired(true);
$productAttribute->setTranslatable(true);
$this->getProductManager()->getStorageManager()->persist($productAttribute);

// to query on product attributes :
$this->getProductManager()->getAttributeExtendedRepository();
```

Note that product attribute mapping provides cascades to create / delet related base attribute too.

AbstractEntityAttributeExtended provides equaly some shortcuts to base attribute accessors (required, unique, etc) to directly manipulate custom attribute.

