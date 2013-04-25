<?php
namespace Oro\Bundle\AddressBundle\Tests\Unit\Type;

use Symfony\Component\Form\Tests\FormIntegrationTestCase;

use Oro\Bundle\AddressBundle\Form\Type\AddressApiType;
use Oro\Bundle\AddressBundle\Entity\Address;
use Symfony\Component\Form\FormInterface;

class AddressApiTypeTest extends FormIntegrationTestCase
{
    /**
     * @var AddressApiType
     */
    protected $type;

    /**
     * @var FormInterface
     */
    protected $form;

    /**
     * Setup test env
     */
    public function setUp()
    {
        parent::setUp();

        $flexibleClass = 'Oro\Bundle\AddressBundle\Entity\Address';
        $entityClass = 'Oro\Bundle\AddressBundle\Entity\Value\AddressValue';

        $this->type = new AddressApiType($flexibleClass, $entityClass);
        $this->form = $this->factory->create($this->type);

    }

    /**
     * Create form
     */
    protected function createForm()
    {
        return $this->factory->create($this->type);
    }

    public function testBindValidData()
    {
        $formData = array(
            'street'      => 'test',
            'city'        => 'test',
            'state'       => 'test',
            'postalCode'  => 'test',
            'country'     => 'test',
        );

        $form = $this->form;

        $address = new Address();
        $address->setStreet($formData['street']);

        $this->assertTrue($form->isSynchronized());
        $form->bind($formData);

        $view = $form->createView();
        $children = $view->children;

        foreach (array_keys($formData) as $key) {
            $this->assertArrayHasKey($key, $children);
        }
    }
}
