<?php
namespace Oro\Bundle\FlexibleEntityBundle\Tests\Entity\Repository;

use Doctrine\Tests\OrmTestCase;
use Doctrine\Common\Annotations\AnnotationReader;
use Doctrine\ORM\Mapping\Driver\DriverChain;
use Doctrine\ORM\Mapping\Driver\AnnotationDriver;
use Oro\Bundle\FlexibleEntityBundle\Entity\Repository\FlexibleEntityRepository;

/**
 * Test related class
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2012 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/MIT MIT
 *
 */
class FlexibleEntityRepositoryTest extends OrmTestCase
{

    /**
     * @var EntityManager
     */
    protected $em;

    /**
     * @var FlexibleEntityRepository
     */
    protected $repository;

    /**
     * @var string
     */
    protected $flexibleClassName;

    /**
     * @var string
     */
    protected $flexibleValueClassName;

    /**
     * @var array
     */
    protected $flexibleConfig;

    /**
     * Prepare test
     */
    protected function setUp()
    {
        // config
        $this->flexibleClassName      = 'Oro\Bundle\FlexibleEntityBundle\Tests\Entity\Demo\Flexible';
        $this->flexibleValueClassName = 'Oro\Bundle\FlexibleEntityBundle\Tests\Entity\Demo\FlexibleAttributeValue';
        $this->flexibleConfig = array(
            'entities_config' => array(
                $this->flexibleClassName => array(
                    'flexible_manager'                      => 'demo_manager',
                    'flexible_entity_class'                 => $this->flexibleClassName,
                    'flexible_entity_value_class'           => $this->flexibleValueClassName,
                    'flexible_attribute_extended_class'     => false,
                    'flexible_attribute_class'              => 'Oro\Bundle\FlexibleEntityBundle\Entity\Attribute',
                    'flexible_attribute_option_class'       => 'Oro\Bundle\FlexibleEntityBundle\Entity\AttributeOption',
                    'flexible_attribute_option_value_class' => 'Oro\Bundle\FlexibleEntityBundle\Entity\AttributeOptionValue'
                )
            )
        );
        // prepare flexible repository
        // see https://symfony2-document.readthedocs.org/en/latest/cookbook/testing/doctrine.html
        $reader = new AnnotationReader();
        $metadataDriver = new AnnotationDriver($reader, 'Oro\\Bundle\\FlexibleEntityBundle\\Entity');
        $this->em = $this->_getTestEntityManager();
        $this->em->getConfiguration()->setMetadataDriverImpl($metadataDriver);
        $metadata = $this->em->getClassMetadata($this->flexibleClassName);
        $this->repository = new FlexibleEntityRepository($this->em, $metadata);
    }

    /**
     * Test related method
     */
    public function testGetLocaleCode()
    {
        $code = 'fr_FR';
        $this->repository->setLocaleCode($code);
        $this->assertEquals($this->repository->getLocaleCode(), $code);
    }

    /**
     * Test related method
     */
    public function testGetScope()
    {
        $code = 'ecommerce';
        $this->repository->setScope($code);
        $this->assertEquals($this->repository->getScope(), $code);
    }

    /**
     * Test related method
     */
    public function testgetFlexibleConfig()
    {
        $this->repository->setFlexibleConfig($this->flexibleConfig);
        $this->assertEquals($this->repository->getFlexibleConfig(), $this->flexibleConfig);
    }

    /**
     * Test related method
     */
    public function testcreateQueryBuilder()
    {
        // with lazy loading
        $qb = $this->repository->createQueryBuilder('MyFlexible', true);
        $expectedSql = 'SELECT MyFlexible FROM Oro\Bundle\FlexibleEntityBundle\Tests\Entity\Demo\Flexible MyFlexible';
        $this->assertEquals($expectedSql, $qb->getQuery()->getDql());
        // without lazy loading
        $qb = $this->repository->createQueryBuilder('MyFlexible');
        $expectedSql = 'SELECT MyFlexible, Value, Attribute'
            .' FROM Oro\Bundle\FlexibleEntityBundle\Tests\Entity\Demo\Flexible MyFlexible'
            .' LEFT JOIN MyFlexible.values Value LEFT JOIN Value.attribute Attribute';
        $this->assertEquals($expectedSql, $qb->getQuery()->getDql());
    }



}
