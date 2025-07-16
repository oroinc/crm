<?php

namespace Oro\Bundle\SalesBundle\Tests\Unit\Model;

use Doctrine\ORM\EntityManager;
use Doctrine\ORM\ORMInvalidArgumentException;
use Oro\Bundle\SalesBundle\Model\ChangeLeadStatus;
use Oro\Bundle\SalesBundle\Tests\Unit\Fixture\LeadStub;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Validator\ConstraintViolationList;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class ChangeLeadStatusTest extends TestCase
{
    private EntityManager $entityManager;
    private LeadStub $lead;
    private ChangeLeadStatus $model;

    #[\Override]
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
            ->willReturn(new ConstraintViolationList());

        $this->model = new ChangeLeadStatus($this->entityManager, $validator);
    }

    public function testDisqualify(): void
    {
        $this->model->disqualify($this->lead);
        $this->assertEquals('lead_status.canceled', $this->lead->getStatus());
    }

    public function testQualify(): void
    {
        $this->model->qualify($this->lead);
        $this->assertEquals('lead_status.qualified', $this->lead->getStatus());
    }

    public function testSuccessQualify(): void
    {
        $this->assertTrue($this->model->qualify($this->lead));
    }

    public function testFailQualify(): void
    {
        $this->entityManager->expects($this->once())
            ->method('persist')
            ->willThrowException(new ORMInvalidArgumentException('test exception'));

        $this->assertFalse($this->model->qualify($this->lead));
    }
}
