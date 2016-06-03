<?php

namespace OroCRM\Bundle\ChannelBundle\Tests\Unit\Form\Type;

use Doctrine\Common\Annotations\AnnotationReader;
use Doctrine\ORM\Mapping\Driver\AnnotationDriver;

use Genemu\Bundle\FormBundle\Form\JQuery\Type\Select2Type;

use Symfony\Component\Form\Forms;
use Symfony\Component\Form\FormFactory;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\PreloadedExtension;

use OroCRM\Bundle\ChannelBundle\Form\Type\ChannelSelectType;
use OroCRM\Bundle\ChannelBundle\Provider\ChannelsByEntitiesProvider;

use Oro\Bundle\TestFrameworkBundle\Test\Doctrine\ORM\OrmTestCase;

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
        $registry       = $this->getMock('Symfony\Bridge\Doctrine\RegistryInterface');
        $metadataDriver = new AnnotationDriver(
            new AnnotationReader(),
            'OroCRM\Bundle\ChannelBundle\Tests\Unit\Stubs\Entity'
        );

        $em     = $this->getTestEntityManager();
        $config = $em->getConfiguration();
        $config->setMetadataDriverImpl($metadataDriver);
        $config->setEntityNamespaces(['OroCRMChannelBundle' => 'OroCRM\Bundle\ChannelBundle\Tests\Unit\Stubs\Entity']);

        $registry->expects($this->any())
            ->method('getManagerForClass')
            ->will($this->returnValue($em));

        $entityType = new EntityType($registry);
        $genemuType = new Select2Type('entity');

        $channelsProvider = $this
            ->getMockBuilder('OroCRM\Bundle\ChannelBundle\Provider\ChannelsByEntitiesProvider')
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
                            $genemuType->getName() => $genemuType
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
            'orocrm_channel_select_type',
            $this->type->getName()
        );
    }

    public function testGetParent()
    {
        $this->assertEquals(
            'genemu_jqueryselect2_entity',
            $this->type->getParent()
        );
    }
}
