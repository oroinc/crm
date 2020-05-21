<?php
namespace Oro\Bundle\MagentoBundle\Tests\Unit\Form\Type;

use Oro\Bundle\FormBundle\Form\Type\CollectionType;
use Oro\Bundle\MagentoBundle\Form\Type\CartItemCollectionType;
use Symfony\Component\OptionsResolver\Options;

class CartItemCollectionTypeTest extends \PHPUnit\Framework\TestCase
{
    /** @var CartItemCollectionType */
    protected $type;

    protected function setUp(): void
    {
        $this->type = new CartItemCollectionType();
    }

    protected function tearDown(): void
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

    public function testGetParent()
    {
        $this->assertEquals(CollectionType::class, $this->type->getParent());
    }
}
