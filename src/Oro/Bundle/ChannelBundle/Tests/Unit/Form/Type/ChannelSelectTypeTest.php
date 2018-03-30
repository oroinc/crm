<?php

namespace Oro\Bundle\ChannelBundle\Tests\Unit\Form\Type;

use Doctrine\Common\Annotations\AnnotationReader;
use Doctrine\ORM\Mapping\Driver\AnnotationDriver;
use Oro\Bundle\ChannelBundle\Form\Type\ChannelSelectType;
use Oro\Bundle\ChannelBundle\Provider\ChannelsByEntitiesProvider;
use Oro\Bundle\FormBundle\Form\Type\Select2EntityType;
use Oro\Bundle\FormBundle\Form\Type\Select2Type;
use Oro\Bundle\TestFrameworkBundle\Test\Doctrine\ORM\OrmTestCase;
use Oro\Component\Testing\Unit\PreloadedExtension;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\FormFactory;
use Symfony\Component\Form\Forms;

class ChannelSelectTypeTest extends OrmTestCase
{
    /** @var ChannelSelectType */
    protected $type;

    /** @var FormFactory */
    protected $factory;

    /**
     * @var ChannelsByEntitiesProvider|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $channelsProvider;

    public function setUp()
    {
        $registry       = $this->createMock('Symfony\Bridge\Doctrine\RegistryInterface');
        $metadataDriver = new AnnotationDriver(
            new AnnotationReader(),
            'Oro\Bundle\ChannelBundle\Tests\Unit\Stubs\Entity'
        );

        $em     = $this->getTestEntityManager();
        $config = $em->getConfiguration();
        $config->setMetadataDriverImpl($metadataDriver);
        $config->setEntityNamespaces(['OroChannelBundle' => 'Oro\Bundle\ChannelBundle\Tests\Unit\Stubs\Entity']);

        $registry->expects($this->any())
            ->method('getManagerForClass')
            ->will($this->returnValue($em));

        $entityType = new EntityType($registry);
        $select2Type = new Select2Type(
            'Symfony\Bridge\Doctrine\Form\Type\EntityType',
            'oro_select2_entity'
        );

        $channelsProvider = $this
            ->getMockBuilder('Oro\Bundle\ChannelBundle\Provider\ChannelsByEntitiesProvider')
            ->disableOriginalConstructor()
            ->getMock();

        $this->type = new ChannelSelectType($channelsProvider);

        $this->factory = Forms::createFormFactoryBuilder()
            ->addExtensions(
                [
                    new PreloadedExtension(
                        [
                            $entityType->getName() => $entityType,
                            $this->type->getName() => $this->type,
                            $select2Type->getName() => $select2Type
                        ],
                        []
                    )
                ]
            )
            ->getFormFactory();
    }

    public function tearDown()
    {
        unset($this->type, $this->factory);
    }

    public function testGetName()
    {
        $this->assertEquals(
            'oro_channel_select_type',
            $this->type->getName()
        );
    }

    public function testGetParent()
    {
        $this->assertEquals(
            Select2EntityType::class,
            $this->type->getParent()
        );
    }
}
