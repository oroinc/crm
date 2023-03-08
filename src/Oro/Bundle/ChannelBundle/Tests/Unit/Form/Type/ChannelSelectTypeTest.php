<?php

namespace Oro\Bundle\ChannelBundle\Tests\Unit\Form\Type;

use Doctrine\Common\Annotations\AnnotationReader;
use Doctrine\ORM\Mapping\Driver\AnnotationDriver;
use Doctrine\Persistence\ManagerRegistry;
use Oro\Bundle\ChannelBundle\Form\Type\ChannelSelectType;
use Oro\Bundle\ChannelBundle\Provider\ChannelsByEntitiesProvider;
use Oro\Bundle\FormBundle\Form\Type\Select2EntityType;
use Oro\Component\Testing\Unit\ORM\OrmTestCase;
use Oro\Component\Testing\Unit\PreloadedExtension;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\FormFactory;
use Symfony\Component\Form\Forms;

class ChannelSelectTypeTest extends OrmTestCase
{
    /** @var ChannelSelectType */
    private $type;

    /** @var FormFactory */
    private $factory;

    protected function setUp(): void
    {
        $registry = $this->createMock(ManagerRegistry::class);

        $em = $this->getTestEntityManager();
        $em->getConfiguration()->setMetadataDriverImpl(new AnnotationDriver(new AnnotationReader()));

        $registry->expects($this->any())
            ->method('getManagerForClass')
            ->willReturn($em);

        $this->type = new ChannelSelectType($this->createMock(ChannelsByEntitiesProvider::class));

        $this->factory = Forms::createFormFactoryBuilder()
            ->addExtensions(
                [
                    new PreloadedExtension(
                        [
                            EntityType::class => new EntityType($registry),
                            $this->type
                        ],
                        []
                    )
                ]
            )
            ->getFormFactory();
    }

    public function testGetParent()
    {
        $this->assertEquals(
            Select2EntityType::class,
            $this->type->getParent()
        );
    }
}
