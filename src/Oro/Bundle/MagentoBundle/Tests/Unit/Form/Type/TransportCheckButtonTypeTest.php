<?php

namespace Oro\Bundle\MagentoBundle\Tests\Unit\Form\Type;

use Symfony\Component\OptionsResolver\OptionsResolver;

use Oro\Bundle\MagentoBundle\Form\Type\TransportCheckButtonType;

class TransportCheckButtonTypeTest extends \PHPUnit_Framework_TestCase
{
    public function testFormInterface()
    {
        $type = new TransportCheckButtonType();

        $this->assertSame('oro_magento_transport_check_button', $type->getName());
        $this->assertNull($type->getParent());
        $this->assertInstanceOf('Symfony\Component\Form\Extension\Core\Type\ButtonType', $type);
    }

    public function testSetDefaultOptions()
    {
        $type = new TransportCheckButtonType();

        $resolver = new OptionsResolver();
        $type->setDefaultOptions($resolver);

        $options = $resolver->resolve();
        $this->assertArrayHasKey('attr', $options);
        $this->assertArrayHasKey('class', $options['attr']);
    }
}
