<?php

namespace Oro\Bundle\MagentoBundle\Tests\Unit\Form\Type;

use Oro\Bundle\MagentoBundle\Form\Type\TransportCheckButtonType;
use Symfony\Component\OptionsResolver\OptionsResolver;

class TransportCheckButtonTypeTest extends \PHPUnit\Framework\TestCase
{
    public function testFormInterface()
    {
        $type = new TransportCheckButtonType();

        $this->assertSame('oro_magento_transport_check_button', $type->getName());
        $this->assertNull($type->getParent());
        $this->assertInstanceOf('Symfony\Component\Form\Extension\Core\Type\ButtonType', $type);
    }

    public function testConfigureOptions()
    {
        $type = new TransportCheckButtonType();

        $resolver = new OptionsResolver();
        $type->configureOptions($resolver);

        $options = $resolver->resolve();
        $this->assertArrayHasKey('attr', $options);
        $this->assertArrayHasKey('class', $options['attr']);
    }
}
