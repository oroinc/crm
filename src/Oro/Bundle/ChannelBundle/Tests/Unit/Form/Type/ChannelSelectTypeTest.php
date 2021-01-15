<?php

namespace Oro\Bundle\ChannelBundle\Tests\Unit\Form\Type;

use Doctrine\Common\Annotations\AnnotationReader;
use Doctrine\ORM\Mapping\Driver\AnnotationDriver;
use Oro\Bundle\ChannelBundle\Form\Type\ChannelSelectType;
use Oro\Bundle\ChannelBundle\Provider\ChannelsByEntitiesProvider;
use Oro\Bundle\FormBundle\Form\Type\Select2EntityType;
use Oro\Component\Testing\Unit\PreloadedExtension;
use Oro\Component\TestUtils\ORM\OrmTestCase;
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
     * @var ChannelsByEntitiesProvider|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $channelsProvider;

    protected function setUp(): void
    {
        $registry = $this->createMock('Doctrine\Persistence\ManagerRegistry');
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
                            EntityType::class => $entityType,
                            $this->type
                        ],
                        []
                    )
                ]
            )
            ->getFormFactory();
    }

    protected function tearDown(): void
    {
        unset($this->type, $this->factory);
    }

    public function testGetParent()
    {
        $this->assertEquals(
            Select2EntityType::class,
            $this->type->getParent()
        );
    }
}
