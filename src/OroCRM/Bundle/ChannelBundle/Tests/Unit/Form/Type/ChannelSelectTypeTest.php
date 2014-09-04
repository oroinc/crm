<?php

namespace OroCRM\Bundle\ChannelBundle\Tests\Unit\Form\Type;

use Doctrine\Common\Annotations\AnnotationReader;
use Doctrine\ORM\Mapping\Driver\AnnotationDriver;

use Genemu\Bundle\FormBundle\Form\JQuery\Type\Select2Type;

use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Bridge\Doctrine\RegistryInterface;
use Symfony\Component\Form\FormFactory;
use Symfony\Component\Form\Forms;
use Symfony\Component\Form\PreloadedExtension;

use OroCRM\Bundle\ChannelBundle\Form\Type\ChannelSelectType;

use Oro\Bundle\TestFrameworkBundle\Test\Doctrine\ORM\OrmTestCase;

class ChannelSelectTypeTest extends OrmTestCase
{
    /** @var ChannelSelectType */
    protected $type;

    /** @var RegistryInterface */
    protected $registry;

    /** @var FormFactory */
    protected $factory;

    public function setUp()
    {
        $this->registry = $this->getMock('Symfony\Bridge\Doctrine\RegistryInterface');
        $metadataDriver = new AnnotationDriver(
            new AnnotationReader(),
            'OroCRM\Bundle\ChannelBundle\Entity'
        );

        $em     = $this->getTestEntityManager();
        $config = $em->getConfiguration();
        $config->setMetadataDriverImpl($metadataDriver);
        $config->setEntityNamespaces(['OroCRMChannelBundle' => 'OroCRM\Bundle\ChannelBundle\Entity']);

        $this->registry->expects($this->any())
            ->method('getManagerForClass')
            ->will($this->returnValue($em));

        $entityType = new EntityType($this->registry);
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
        unset($this->type);
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
        $this->type = $this->factory->create(ChannelSelectType::NAME, null, $config);

        $this->assertSame($query, $this->type->getConfig()->getOption('query_builder')->getDQL());
    }

    // @codingStandardsIgnoreStart
    public function dataProvider()
    {
        return [
            'without entities' => [
                'config' => [
                    'entities' => []
                ],
                'query'  => 'SELECT c FROM OroCRM\Bundle\ChannelBundle\Entity\Channel c WHERE c.status = :status ORDER BY c.name ASC'
            ],
            'with entities'    => [
                'config' => [
                    'entities' => [
                        'entity1',
                        'entity2'
                    ]
                ],
                'query'  => 'SELECT c FROM OroCRM\Bundle\ChannelBundle\Entity\Channel c INNER JOIN c.entities e WHERE e.name IN(\'entity1\', \'entity2\') AND c.status = :status GROUP BY c.name HAVING COUNT(DISTINCT e.name) = :count ORDER BY c.name ASC'
            ]
        ];
    }
    // @codingStandardsIgnoreEnd
}
