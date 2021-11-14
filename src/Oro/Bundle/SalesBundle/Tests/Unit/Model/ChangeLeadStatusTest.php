<?php

namespace Oro\Bundle\SalesBundle\Tests\Unit\Model;

use Doctrine\ORM\EntityManager;
use Doctrine\ORM\ORMInvalidArgumentException;
use Oro\Bundle\SalesBundle\Model\ChangeLeadStatus;
use Oro\Bundle\SalesBundle\Tests\Unit\Fixture\LeadStub;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class ChangeLeadStatusTest extends \PHPUnit\Framework\TestCase
{
    /** @var EntityManager */
    private $entityManager;

    /** @var LeadStub */
    private $lead;

    /** @var ChangeLeadStatus */
    private $model;

    protected function setUp(): void
    {
        $this->entityManager = $this->createMock(EntityManager::class);
        $this->lead = new LeadStub();

        $this->entityManager->expects($this->once())
            ->method('getReference')
            ->willReturnCallback(function ($statusClass, $statusCode) {
                return $statusCode;
            });

        $validator = $this->createMock(ValidatorInterface::class);
        $validator->expects($this->any())
            ->method('validate')
            ->willReturn($this->createMock(\Countable::class));

        $this->model = new ChangeLeadStatus($this->entityManager, $validator);
    }

    public function testDisqualify()
    {
        $this->model->disqualify($this->lead);
        $this->assertEquals('canceled', $this->lead->getStatus());
    }

    public function testQualify()
    {
        $this->model->qualify($this->lead);
        $this->assertEquals('qualified', $this->lead->getStatus());
    }

    public function testSuccessQualify()
    {
        $this->assertTrue($this->model->qualify($this->lead));
    }

    public function testFailQualify()
    {
        $this->entityManager->expects($this->once())
            ->method('persist')
            ->willThrowException(new ORMInvalidArgumentException('test exception'));

        $this->assertFalse($this->model->qualify($this->lead));
    }
}
