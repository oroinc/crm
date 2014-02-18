<?php

namespace OroCRM\Bundle\ContactUsBundle\Tests\Unit\Form\Type;

use Symfony\Component\Form\PreloadedExtension;
use Symfony\Component\Form\Test\TypeTestCase;

use Oro\Bundle\EmbeddedFormBundle\Form\Type\ChannelAwareFormType;
use Oro\Bundle\EmbeddedFormBundle\Form\Type\EmbeddedFormInterface;

use OroCRM\Bundle\ContactUsBundle\Form\Type\ContactRequestType;

class ContactRequestTypeTest extends TypeTestCase
{
    /** @var ContactRequestType */
    protected $formType;

    public function setUp()
    {
        parent::setUp();
        $this->formType = new ContactRequestType();
    }

    public function tearDown()
    {
        parent::tearDown();
        unset($this->formType);
    }

    protected function getExtensions()
    {
        $mockEntityManager = $this->getMockBuilder('\Doctrine\ORM\EntityManager')
            ->disableOriginalConstructor()->getMock();

        $mockMetadata = $this->getMockBuilder('Doctrine\Common\Persistence\Mapping\ClassMetadata')
            ->disableOriginalConstructor()->getMock();

        $mockEntityManager->expects($this->any())->method('getClassMetadata')
            ->will($this->returnValue($mockMetadata));

        $mockRegistry = $this->getMockBuilder('Doctrine\Bundle\DoctrineBundle\Registry')
            ->disableOriginalConstructor()
            ->setMethods(['getManagerForClass'])
            ->getMock();

        $mockRegistry->expects($this->any())->method('getManagerForClass')
            ->will($this->returnValue($mockEntityManager));

        $mockEntityType = $this->getMockBuilder('Symfony\Bridge\Doctrine\Form\Type\EntityType')
            ->setMethods(['getName', 'buildForm'])
            ->setConstructorArgs([$mockRegistry])
            ->getMock();

        $mockEntityType->expects($this->any())->method('getName')
            ->will($this->returnValue('entity'));

        $mockRepo = $this->getMockBuilder('Doctrine\ORM\EntityRepository')
            ->disableOriginalConstructor()
            ->getMock();

        $mockEntityManager->expects($this->any())
            ->method('getRepository')
            ->will($this->returnValue($mockRepo));

        $mockRepo->expects($this->any())->method('findAll')
            ->will($this->returnValue([]));

        $parentType = new ChannelAwareFormType();

        return [
            new PreloadedExtension(
                array(
                    $parentType->getName()     => $parentType,
                    $mockEntityType->getName() => $mockEntityType
                ),
                array()
            )
        ];
    }

    public function testHasName()
    {
        $this->assertEquals('orocrm_contactus_contact_request', $this->formType->getName());
    }

    public function testHasChannelAwareParent()
    {
        $this->assertEquals('oro_channel_aware_form', $this->formType->getParent());
    }

    public function testImplementEmbeddedFormInterface()
    {
        $this->assertTrue($this->formType instanceof EmbeddedFormInterface);

        $this->assertNotEmpty($this->formType->getDefaultCss());
        $this->assertInternalType('string', $this->formType->getDefaultCss());

        $this->assertNotEmpty($this->formType->getDefaultSuccessMessage());
        $this->assertInternalType('string', $this->formType->getDefaultSuccessMessage());

        $this->assertNotEmpty($this->formType->geFormLayout());
        $this->assertInternalType('string', $this->formType->geFormLayout());
    }

    public function testBuildForm()
    {
        $form = $this->factory->create($this->formType, null);

        $this->assertSame(
            'OroCRM\Bundle\ContactUsBundle\Entity\ContactRequest',
            $form->getConfig()->getOption('data_class')
        );

        $fields = [
            'firstName',
            'lastName',
            'emailAddress',
            'phone',
            'comment',
            'submit'
        ];
        foreach ($fields as $field) {
            $this->assertTrue($form->has($field), sprintf('Form should have: %s child', $field));
        }
    }
}
