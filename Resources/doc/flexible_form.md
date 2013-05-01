Create flexible entity form
===========================

By extending basic form types, you can quickly create a create / edit form for your flexible entity.

Create flexible entity form
---------------------------

```php
<?php
namespace Pim\Bundle\ProductBundle\Form\Type;

use Oro\Bundle\FlexibleEntityBundle\Form\Type\FlexibleType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class ProductType extends FlexibleType
{
    public function addEntityFields(FormBuilderInterface $builder)
    {
        parent::addEntityFields($builder);

        $builder->add('sku', 'text');
    }
}
```

Create flexible value form
--------------------------

```php
<?php
namespace Pim\Bundle\ProductBundle\Form\Type;

use Oro\Bundle\FlexibleEntityBundle\Form\Type\FlexibleValueType;

class ProductValueType extends FlexibleValueType
{
    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'pim_product_value';
    }
}

Declare form as service
-----------------------

```yaml
    form.type.pim_product_value:
        class: Pim\Bundle\ProductBundle\Form\Type\ProductValueType
        arguments: [@product_manager, %product_attribute_value_class%]
        tags:
            - { name: form.type, alias: pim_product_value }
```

Use from controller
-------------------

```php
    public function editAction(Product $entity)
    {
        $request = $this->getRequest();

        // create form
        $entClassName = $this->getProductManager()->getFlexibleName();
        $valueClassName = $this->getProductManager()->getFlexibleValueName();
        $form = $this->createForm(new ProductType($this->getProductManager(), 'pim_product_value'), $entity);

        if ($request->getMethod() == 'POST') {
            $form->bind($request);
            if ($form->isValid()) {
                $em = $this->getProductManager()->getStorageManager();
                $em->persist($entity);
                $em->flush();
                $this->get('session')->getFlashBag()->add('success', 'Successfully saved');

                return $this->redirect($this->generateUrl('acme_demo_customer_index'));
            }
        }

        return array(
            'form' => $form->createView(),
        );
    }
```

Note that you can also define product form as service in configuration and use directly service from controller
