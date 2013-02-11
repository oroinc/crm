Create flexible form
====================

You can extends FlexibleType to easily obtain a dynamlic form based on your flexible entity :

```php
<?php
namespace Acme\Bundle\DemoFlexibleEntityBundle\Form\Type;

use Oro\Bundle\FlexibleEntityBundle\Form\Type\FlexibleType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

/**
 * Customer form type
 */
class CustomerType extends FlexibleType
{

    /**
     * {@inheritdoc}
     */
    public function addEntityFields(FormBuilderInterface $builder)
    {
        // add default flexible fields
        parent::addEntityFields($builder);
        // customer fields
        $builder->add('firstname', 'text');
        $builder->add('lastname', 'text');
        $builder->add('email', 'email', array('required' => true));
    }

}
```

Then you can use it on your controller as :
```php
    public function editAction(Customer $entity)
    {
        $request = $this->getRequest();

        // create form
        $entClassName = $this->getCustomerManager()->getFlexibleName();
        $valueClassName = $this->getCustomerManager()->getFlexibleValueName();
        $form = $this->createForm(new CustomerType($entClassName, $valueClassName), $entity);

        if ($request->getMethod() == 'POST') {
            $form->bind($request);

            if ($form->isValid()) {
                $em = $this->getCustomerManager()->getStorageManager();
                $em->persist($entity);
                $em->flush();

                $this->get('session')->getFlashBag()->add('success', 'Customer successfully saved');

                return $this->redirect($this->generateUrl('acme_demo_customer_index'));
            }
        }

        return array(
            'form' => $form->createView(),
        );
    }
```

Note that you can also define it as service in configuration and use service from controller

Create attribute form
=====================

You can also use attribute type provided to define your own attribute management.


