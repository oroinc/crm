<?php

namespace Oro\Bundle\GridBundle\Tests\Unit\Filter\ORM;

use Symfony\Component\Translation\TranslatorInterface;
use Doctrine\ORM\Query\Expr\Comparison;

abstract class FilterTestCase extends \PHPUnit_Framework_TestCase
{
    /**
     * @var array
     */
    protected $filterTypes = array();

    /**
     * @var string
     */
    protected $actualCondition;

    protected function setUp()
    {
        $this->markTestSkipped();
    }

    /**
     * @return TranslatorInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected function getTranslatorMock()
    {
        $translator = $this->getMockForAbstractClass(
            'Symfony\Component\Translation\TranslatorInterface',
            array(),
            '',
            false,
            true,
            true,
            array('trans')
        );
        $translator->expects($this->any())
            ->method('trans')
            ->will($this->returnArgument(0));

        return $translator;
    }

    /**
     * @param array $typeOptions
     */
    protected function assertTypeOptions(array $typeOptions)
    {
        $this->assertSameSize($this->filterTypes, $typeOptions);
        foreach ($this->filterTypes as $type) {
            $this->assertArrayHasKey($type, $typeOptions);
            $this->assertNotEmpty($typeOptions[$type]);
        }
    }

    /**
     * Callback for QueryBuilder::andWhere
     *
     * @param Comparison $comparison
     */
    public function andWhereCallback(Comparison $comparison)
    {
        $this->actualCondition
            = $comparison->getLeftExpr() . ' ' . $comparison->getOperator() . ' ' . $comparison->getRightExpr();
    }
}
