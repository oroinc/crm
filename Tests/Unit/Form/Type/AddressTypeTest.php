<?php
namespace Oro\Bundle\AddressBundle\Tests\Unit\Type;

use Symfony\Component\Form\Tests\FormIntegrationTestCase;

use Oro\Bundle\AddressBundle\Form\Type\AddressType;
use Oro\Bundle\AddressBundle\Entity\Address;

class AddressTypeTest extends FormIntegrationTestCase
{
    public function testBindValidData()
    {
        $formData = array(
            'street' => 'No way, no way',
        );

        $flexibleClass = 'Oro\Bundle\FlexibleEntityBundle\Manager\FlexibleManager';
        $entityClass = 'Oro\Bundle\AddressBundle\Entity\Value\AddressValue';

        $type = new AddressType($flexibleClass, $entityClass);
        $form = $this->factory->create($type);

        $address = new Address();
        $address->setStreet($formData['street']);

        $this->assertTrue($form->isSynchronized());

        $view = $form->createView();
        $children = $view->children;

        foreach (array_keys($formData) as $key) {
            $this->assertArrayHasKey($key, $children);
        }
    }
}
