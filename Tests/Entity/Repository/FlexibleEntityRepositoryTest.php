<?php
namespace Oro\Bundle\FlexibleEntityBundle\Tests\Entity\Repository;

use Oro\Bundle\FlexibleEntityBundle\Model\AbstractAttributeType;

use Oro\Bundle\FlexibleEntityBundle\Entity\Attribute;

use Oro\Bundle\FlexibleEntityBundle\Tests\AbstractFlexibleEntityManagerTest;
use Oro\Bundle\FlexibleEntityBundle\Entity\Repository\FlexibleEntityRepository;

/**
 * Test related class
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2012 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/MIT MIT
 *
 */
class FlexibleEntityRepositoryTest extends AbstractFlexibleEntityManagerTest
{

    /**
     * @var FlexibleEntityRepository
     */
    protected $repository;

    /**
     * Prepare test
     */
    public function setUp()
    {
        parent::setUp();
        // create a mock of repository (mock only getCodeToAttributes method)
        $metadata = $this->entityManager->getClassMetadata($this->flexibleClassName);
        $constructorArgs = array($this->entityManager, $metadata);
        $this->repository = $this->getMock(
            'Oro\Bundle\FlexibleEntityBundle\Entity\Repository\FlexibleEntityRepository',
            array('getCodeToAttributes'),
            $constructorArgs
        );
        $this->repository->setLocale($this->defaultLocale);
        $this->repository->setScope($this->defaultScope);
        // prepare return of getCodeToAttributes calls
        // attribute name
        $attributeName = $this->manager->createAttribute();
        $attributeName->setId(1);
        $attributeName->setCode('name');
        $attributeName->setBackendType(AbstractAttributeType::BACKEND_TYPE_VARCHAR);
        $this->entityManager->persist($attributeName);
        $attributeName->setTranslatable(true);
        // attribute desc
        $attributeDesc = $this->manager->createAttribute();
        $attributeDesc->setId(2);
        $attributeDesc->setCode('description');
        $attributeDesc->setBackendType(AbstractAttributeType::BACKEND_TYPE_TEXT);
        $this->entityManager->persist($attributeDesc);
        $attributeDesc->setTranslatable(true);
        $attributeDesc->setScopable(true);
        // method return
        $return = array($attributeName->getCode() => $attributeName, $attributeDesc->getCode() => $attributeDesc);
        $this->repository->expects($this->any())->method('getCodeToAttributes')->will($this->returnValue($return));
    }

    /**
     * Test related method
     */
    public function testGetLocale()
    {
        $code = 'fr_FR';
        $this->repository->setLocale($code);
        $this->assertEquals($this->repository->getLocale(), $code);
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
        $expectedDql = 'SELECT MyFlexible, Value, Attribute'
            .' FROM Oro\Bundle\FlexibleEntityBundle\Tests\Entity\Demo\Flexible MyFlexible'
            .' LEFT JOIN MyFlexible.values Value LEFT JOIN Value.attribute Attribute';
        $this->assertEquals($expectedDql, $qb->getQuery()->getDql());
    }

    /**
     * Test related method
     */
    public function testPrepareQueryBuilder()
    {
        $attToSelect  = array('name', 'description');
        $attCriterias = array('id' => '123', 'name' => 'my name', 'description' => 'my description');
        $attOrderBy   = array('description' => 'desc', 'id' => 'asc');

        // find all
        $qb = $this->repository->prepareQueryBuilder();
        $expectedDql = 'SELECT Entity, Value, Attribute '
            .'FROM Oro\Bundle\FlexibleEntityBundle\Tests\Entity\Demo\Flexible Entity '
            .'LEFT JOIN Entity.values Value LEFT JOIN Value.attribute Attribute';
        $this->assertEquals($expectedDql, $qb->getQuery()->getDql());

        // add select attributes
        $qb = $this->repository->prepareQueryBuilder($attToSelect);
        $expectedDql = 'SELECT Entity, selectVname, selectVdescription '
            .'FROM Oro\Bundle\FlexibleEntityBundle\Tests\Entity\Demo\Flexible Entity '
            .'LEFT JOIN Entity.values selectVname WITH selectVname.attribute = 1 '
            .'LEFT JOIN Entity.values selectVdescription WITH selectVdescription.attribute = 2';
        $this->assertEquals($expectedDql, $qb->getQuery()->getDql());

        // add select attributes and criterias
        $qb = $this->repository->prepareQueryBuilder($attToSelect, $attCriterias);
        $expectedDql = 'SELECT Entity, selectVname, selectVdescription '
            .'FROM Oro\Bundle\FlexibleEntityBundle\Tests\Entity\Demo\Flexible Entity '
            .'INNER JOIN Entity.values filterVname WITH filterVname.attribute = 1 AND filterVname.varchar = :filtervname AND filterVname.locale = :filterLname '
            .'INNER JOIN Entity.values filterVdescription WITH filterVdescription.attribute = 2 AND filterVdescription.text = :filtervdescription AND filterVdescription.locale = :filterLdescription AND filterVdescription.scope = :filterSdescription '
            .'LEFT JOIN Entity.values selectVname WITH selectVname.attribute = 1 '
            .'LEFT JOIN Entity.values selectVdescription WITH selectVdescription.attribute = 2 '
            .'WHERE Entity.id = :id';
        $this->assertEquals($expectedDql, $qb->getQuery()->getDql());

        // add select attributes and order ny
        $qb = $this->repository->prepareQueryBuilder($attToSelect, null, $attOrderBy);
        $expectedDql = 'SELECT Entity, selectVname, selectVdescription '
            .'FROM Oro\Bundle\FlexibleEntityBundle\Tests\Entity\Demo\Flexible Entity '
            .'LEFT JOIN Entity.values selectVname WITH selectVname.attribute = 1 '
            .'LEFT JOIN Entity.values selectVdescription WITH selectVdescription.attribute = 2 AND selectVdescription.locale = :selectLdescription AND selectVdescription.scope = :selectSdescription '
            .'ORDER BY selectVdescription.text desc, Entity.id asc';
        $this->assertEquals($expectedDql, $qb->getQuery()->getDql());
    }

}
