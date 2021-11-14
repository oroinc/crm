<?php
namespace Oro\Bundle\ContactBundle\Tests\Unit\Form\Type;

use Oro\Bundle\ContactBundle\Form\Type\ContactSelectType;
use Oro\Bundle\FormBundle\Form\Type\OroEntitySelectOrCreateInlineType;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ContactSelectTypeTest extends \PHPUnit\Framework\TestCase
{
    /** @var ContactSelectType */
    private $type;

    protected function setUp(): void
    {
        $this->type = new ContactSelectType();
    }

    public function testConfigureOptions()
    {
        $resolver = $this->createMock(OptionsResolver::class);
        $resolver->expects($this->once())
            ->method('setDefaults')
            ->with($this->isType('array'));
        $this->type->configureOptions($resolver);
    }

    public function testGetParent()
    {
        $this->assertEquals(OroEntitySelectOrCreateInlineType::class, $this->type->getParent());
    }

    public function testGetName()
    {
        $this->assertEquals('oro_contact_select', $this->type->getName());
    }
}
