<?php

namespace OroCRM\Bundle\MagentoBundle\Tests\Unit\Validator;

use Doctrine\Common\Persistence\ManagerRegistry;
use Doctrine\ORM\Query\Expr;
use Doctrine\ORM\QueryBuilder;

use Symfony\Component\Validator\ExecutionContextInterface;

use Oro\Bundle\IntegrationBundle\Entity\Channel;

use OroCRM\Bundle\MagentoBundle\Entity\MagentoSoapTransport;
use OroCRM\Bundle\MagentoBundle\Validator\Constraints\StartSyncDateConstraint;
use OroCRM\Bundle\MagentoBundle\Validator\StartSyncDateValidator;

class StartSyncDateValidatorTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var StartSyncDateValidator
     */
    protected $validator;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|ManagerRegistry
     */
    protected $registry;

    protected function setUp()
    {
        $this->registry = $this->getMock('Doctrine\Common\Persistence\ManagerRegistry');
        $this->validator = new StartSyncDateValidator($this->registry);
    }

    protected function tearDown()
    {
        unset($this->registry, $this->validator);
    }

    /**
     * @param mixed $value
     * @param mixed $formData
     * @param mixed $queryResult
     * @param bool $expectsViolation
     *
     * @dataProvider valueDataProvider
     */
    public function testValidate($value, $formData, $queryResult = null, $expectsViolation = false)
    {
        /** @var \PHPUnit_Framework_MockObject_MockObject|ExecutionContextInterface $context */
        $context = $this->getMock('Symfony\Component\Validator\ExecutionContextInterface');

        $form = $this->getMock('Symfony\Component\Form\FormInterface');
        $form->expects($this->any())->method('getData')->willReturn($formData);
        $context->expects($this->any())->method('getRoot')->willReturn($form);
        $context->expects($expectsViolation ? $this->once() : $this->never())
            ->method('addViolationAt')->willReturn($form);

        $repository = $this->getMockBuilder('Doctrine\ORM\EntityRepository')
            ->disableOriginalConstructor()
            ->getMock();

        $repository->expects($this->any())->method('createQueryBuilder')
            ->willReturn($this->getQueryBuilder($queryResult));

        $this->registry->expects($this->any())
            ->method('getRepository')
            ->will($this->returnValue($repository));

        $this->validator->initialize($context);
        $this->validator->validate($value, new StartSyncDateConstraint());
    }

    /**
     * @return array
     */
    public function valueDataProvider()
    {
        return [
            [new \stdClass(), $this->getIntegration()],
            [new \DateTime(), new \stdClass()],
            [new \DateTime(), $this->getIntegration('Oro\Bundle\IntegrationBundle\Entity\Transport')],
            [new \DateTime(), $this->getIntegration('OroCRM\Bundle\MagentoBundle\Entity\MagentoSoapTransport')],
            [new \DateTime(), $this->getIntegration('OroCRM\Bundle\MagentoBundle\Entity\MagentoSoapTransport', 1)],
            [
                new \DateTime(),
                $this->getIntegration('OroCRM\Bundle\MagentoBundle\Entity\MagentoSoapTransport', 1),
                '2014-12-12',
                true,
            ],
            [
                new \DateTime('2014-12-01'),
                $this->getIntegration('OroCRM\Bundle\MagentoBundle\Entity\MagentoSoapTransport', 1),
                '2014-12-12',
            ],
        ];
    }

    /**
     * @param null|string $transportClass
     * @param null|int $transportId
     * @param string $integrationClass
     *
     * @return Channel
     */
    protected function getIntegration(
        $transportClass = null,
        $transportId = null,
        $integrationClass = 'Oro\Bundle\IntegrationBundle\Entity\Channel'
    ) {
        /** @var Channel $integration */
        $integration = new $integrationClass();

        if ($transportClass) {
            /** @var \PHPUnit_Framework_MockObject_MockObject|MagentoSoapTransport $transport */
            $transport = $this->getMock($transportClass);
            if ($transportId) {
                $transport->expects($this->any())->method('getId')->willReturn($transportId);
            }
            $integration->setTransport($transport);
        }

        return $integration;
    }

    /**
     * @param mixed $result
     *
     * @return \PHPUnit_Framework_MockObject_MockObject|QueryBuilder
     */
    protected function getQueryBuilder($result = null)
    {
        $qb = $this->getMockBuilder('Doctrine\ORM\QueryBuilder')
            ->setMethods(['select', 'where', 'expr', 'setParameter', 'getQuery', 'setFirstResult', 'setMaxResults'])
            ->disableOriginalConstructor()->getMockForAbstractClass();
        $qb->expects($this->any())->method('select')->will($this->returnSelf());
        $qb->expects($this->any())->method('where')->will($this->returnSelf());
        $qb->expects($this->any())->method('setParameter')->will($this->returnSelf());
        $qb->expects($this->any())->method('setFirstResult')->will($this->returnSelf());
        $qb->expects($this->any())->method('setMaxResults')->will($this->returnSelf());
        $qb->expects($this->any())->method('expr')->willReturn(new Expr());

        $query = $this->getMockBuilder('Doctrine\ORM\AbstractQuery')
            ->setMethods(['getSingleScalarResult'])
            ->disableOriginalConstructor()->getMockForAbstractClass();
        $query->expects($this->any())->method('getSingleScalarResult')->willReturn($result);
        $qb->expects($this->any())->method('getQuery')->willReturn($query);

        return $qb;
    }
}
