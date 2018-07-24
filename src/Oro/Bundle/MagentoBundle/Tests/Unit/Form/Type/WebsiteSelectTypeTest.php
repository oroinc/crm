<?php

namespace Oro\Bundle\MagentoBundle\Tests\Unit\Form\Type;

use Oro\Bundle\MagentoBundle\Form\Type\WebsiteSelectType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\OptionsResolver\OptionsResolver;

class WebsiteSelectTypeTest extends \PHPUnit\Framework\TestCase
{
    public function testFormInterface()
    {
        $type = new WebsiteSelectType();

        $this->assertSame('oro_magento_website_select', $type->getName());
        $this->assertSame(ChoiceType::class, $type->getParent());
    }

    public function testConfigureOptions()
    {
        $type = new WebsiteSelectType();

        $resolver = new OptionsResolver();
        $type->configureOptions($resolver);

        $options = $resolver->resolve();
        $this->assertArrayHasKey('tooltip', $options);
    }
}
