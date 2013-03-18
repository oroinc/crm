<?php
namespace Oro\Bundle\FlexibleEntityBundle\Tests\Doctrine\ORM;

use Oro\Bundle\FlexibleEntityBundle\Tests\AbstractOrmTest;
use Doctrine\Tests\OrmTestCase;
use Doctrine\Common\Annotations\AnnotationReader;
use Doctrine\ORM\Mapping\Driver\AnnotationDriver;
use Oro\Bundle\FlexibleEntityBundle\Doctrine\ORM\FlexibleQueryBuilder;
use Oro\Bundle\FlexibleEntityBundle\Model\AbstractAttributeType;
use Oro\Bundle\FlexibleEntityBundle\Entity\Attribute;

/**
 * Test related class
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2012 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/MIT MIT
 *
 */
class FlexibleQueryBuilderTest extends AbstractOrmTest
{
    /**
     * @var FlexibleQueryBuilder
     */
    protected $queryBuilder;

    /**
     * Prepare test
     */
    public function setUp()
    {
        parent::setUp();
        $this->queryBuilder = new FlexibleQueryBuilder($this->entityManager);
    }

    /**
     * Test related method
     */
    public function testGetLocale()
    {
        $code = 'fr_FR';
        $this->queryBuilder->setLocale($code);
        $this->assertEquals($this->queryBuilder->getLocale(), $code);
    }

    /**
     * Test related method
     */
    public function testGetScope()
    {
        $code = 'ecommerce';
        $this->queryBuilder->setScope($code);
        $this->assertEquals($this->queryBuilder->getScope(), $code);
    }

    /**
     * Test related method
     * @expectedException \Oro\Bundle\FlexibleEntityBundle\Exception\FlexibleQueryException
     */
    public function testGetAllowedOperatorsException()
    {
        $this->queryBuilder->getAllowedOperators('unknowBackendType');
    }

    /**
     * Test related method
     */
    public function testGetAllowedOperators()
    {
        $operators = $this->queryBuilder->getAllowedOperators(AbstractAttributeType::BACKEND_TYPE_INTEGER);
        $this->assertEquals($operators, array('=', '<', '<=', '>', '>='));
    }

    /**
     * Test related method
     */
    public function testPrepareAttributeJoinCondition()
    {
        $this->queryBuilder->setLocale('fr_FR');
        $this->queryBuilder->setScope('eco');

        $attribute = new Attribute();
        $attribute->setId(12);
        $condition = $this->queryBuilder->prepareAttributeJoinCondition($attribute, 'alias');
        $this->assertEquals($condition, 'alias.attribute = 12');

        $attribute->setTranslatable(true);
        $condition = $this->queryBuilder->prepareAttributeJoinCondition($attribute, 'alias');
        $this->assertEquals($condition, "alias.attribute = 12 AND alias.locale = 'fr_FR'");

        $attribute->setScopable(true);
        $condition = $this->queryBuilder->prepareAttributeJoinCondition($attribute, 'alias');
        $this->assertEquals($condition, "alias.attribute = 12 AND alias.locale = 'fr_FR' AND alias.scope = 'eco'");
    }

    /**
     * Test related method
     * @expectedException \Oro\Bundle\FlexibleEntityBundle\Exception\FlexibleQueryException
     */
    public function testPrepareAttributeJoinConditionExceptionLocale()
    {
        $attribute = new Attribute();
        $attribute->setTranslatable(true);
        $this->queryBuilder->prepareAttributeJoinCondition($attribute, 'alias');
    }

    /**
     * Test related method
     * @expectedException \Oro\Bundle\FlexibleEntityBundle\Exception\FlexibleQueryException
     */
    public function testPrepareAttributeJoinConditionExceptionScope()
    {
        $attribute = new Attribute();
        $attribute->setScopable(true);
        $this->queryBuilder->prepareAttributeJoinCondition($attribute, 'alias');
    }
}
