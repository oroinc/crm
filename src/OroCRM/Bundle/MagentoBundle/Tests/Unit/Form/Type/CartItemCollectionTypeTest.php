<?php
namespace OroCRM\Bundle\MagentoBundle\Tests\Unit\Form\Type;

use OroCRM\Bundle\MagentoBundle\Form\Type\CartItemCollectionType;
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

    public function testSetDefaultOptions()
    {
        $resolver = $this->getMock('Symfony\Component\OptionsResolver\OptionsResolverInterface');
        $resolver->expects($this->once())
            ->method('setNormalizers')
            ->with(
                [
                    'options' => function (Options $options, $values) {
                        if (!$values) {
                            $values = [];
                        }
                        $values['single_form'] = false;

                        return $values;
                    }
                ]
            );

        $this->type->setDefaultOptions($resolver);
    }

    public function testGetName()
    {
        $this->assertEquals('orocrm_cart_item_collection', $this->type->getName());
    }

    public function testGetParent()
    {
        $this->assertEquals('oro_collection', $this->type->getParent());
    }
}
