<?php
namespace Oro\Bundle\MagentoBundle\Tests\Unit\Form\Type;

use Oro\Bundle\MagentoBundle\Form\Type\CartItemCollectionType;
use Symfony\Component\OptionsResolver\Options;

class CartItemCollectionTypeTest extends \PHPUnit_Framework_TestCase
{
    /** @var CartItemCollectionType */
    protected $type;

    protected function setUp()
    {
        $this->type = new CartItemCollectionType();
    }

    protected function tearDown()
    {
        unset($this->type);
    }

    public function testConfigureOptions()
    {
        $resolver = $this->createMock('Symfony\Component\OptionsResolver\OptionsResolver');
        $resolver->expects($this->once())
            ->method('setNormalizer')
            ->with(
                'entry_options',
                function (Options $options, $values) {
                    if (!$values) {
                        $values = [];
                    }
                    $values['single_form'] = false;

                    return $values;
                }
            );

        $this->type->configureOptions($resolver);
    }

    public function testGetName()
    {
        $this->assertEquals('oro_cart_item_collection', $this->type->getName());
    }

    public function testGetParent()
    {
        $this->assertEquals('oro_collection', $this->type->getParent());
    }
}
