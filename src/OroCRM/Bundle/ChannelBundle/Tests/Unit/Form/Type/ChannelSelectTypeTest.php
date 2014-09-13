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

use Oro\Bundle\TestFrameworkBundle\Test\Doctrine\ORM\OrmTestCase;

class ChannelSelectTypeTest extends OrmTestCase
{
    /** @var ChannelSelectType */
    protected $type;

    /** @var FormFactory */
    protected $factory;

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
        $this->type = new ChannelSelectType();

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

    /**
     * @dataProvider dataProvider
     *
     * @param array  $config
     * @param string $query
     */
    public function testSetDefaultOptions($config, $query)
    {
        $field = $this->factory->create($this->type, null, $config);

        $this->assertSame($query, $field->getConfig()->getOption('query_builder')->getDQL());
    }

    /**
     * @return array
     */
    public function dataProvider()
    {
        return [
            'without entities' => [
                'config' => [
                    'entities' => []
                ],
                'query'  => 'SELECT c FROM OroCRM\Bundle\ChannelBundle\Tests\Unit\Stubs\Entity\Channel c' .
                    ' WHERE c.status = :status ORDER BY c.name ASC'
            ],
            'with entities'    => [
                'config' => [
                    'entities' => [
                        'entity1',
                        'entity2'
                    ]
                ],
                'query'  => 'SELECT c FROM OroCRM\Bundle\ChannelBundle\Tests\Unit\Stubs\Entity\Channel c ' .
                    'INNER JOIN c.entities e ' .
                    'WHERE e.name IN(\'entity1\', \'entity2\') AND c.status = :status GROUP BY c.name, c.id ' .
                    'HAVING COUNT(DISTINCT e.name) = :count ORDER BY c.name ASC'
            ]
        ];
    }
}
