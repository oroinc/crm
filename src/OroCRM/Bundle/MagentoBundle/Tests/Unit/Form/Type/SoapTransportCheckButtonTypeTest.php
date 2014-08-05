<?php

namespace OroCRM\Bundle\MagentoBundle\Tests\Unit\Form\Type;

use Symfony\Component\OptionsResolver\OptionsResolver;

use OroCRM\Bundle\MagentoBundle\Form\Type\SoapTransportCheckButtonType;

class SoapTransportCheckButtonTypeTest extends \PHPUnit_Framework_TestCase
{
    public function testFormInterface()
    {
        $type = new SoapTransportCheckButtonType();

        $this->assertSame('orocrm_magento_soap_transport_check_button', $type->getName());
        $this->assertNull($type->getParent());
        $this->assertInstanceOf('Symfony\Component\Form\Extension\Core\Type\ButtonType', $type);
    }

    public function testSetDefaultOptions()
    {
        $type = new SoapTransportCheckButtonType();

        $resolver = new OptionsResolver();
        $type->setDefaultOptions($resolver);

        $options = $resolver->resolve();
        $this->assertArrayHasKey('attr', $options);
        $this->assertArrayHasKey('class', $options['attr']);
    }
}
