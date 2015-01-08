<?php

namespace OroCRM\Bundle\TaskBundle\Tests\Unit\Entity\Repository;

use Doctrine\Common\Annotations\AnnotationReader;
use Doctrine\ORM\Mapping\Driver\AnnotationDriver;

use Oro\Bundle\TestFrameworkBundle\Test\Doctrine\ORM\OrmTestCase;
use Oro\Bundle\TestFrameworkBundle\Test\Doctrine\ORM\Mocks\EntityManagerMock;
use OroCRM\Bundle\TaskBundle\Entity\Repository\TaskRepository;

class TaskRepositoryTest extends OrmTestCase
{
    /** @var EntityManagerMock */
    protected $em;

    protected function setUp()
    {
        $metadataDriver = new AnnotationDriver(
            new AnnotationReader(),
            'OroCRM\Bundle\TaskBundle\Tests\Unit\Fixtures\Entity'
        );

        $this->em = $this->getTestEntityManager();
        $this->em->getConfiguration()->setMetadataDriverImpl($metadataDriver);
        $this->em->getConfiguration()->setEntityNamespaces(
            [
                'OroCRMTaskBundle' => 'OroCRM\Bundle\TaskBundle\Tests\Unit\Fixtures\Entity'
            ]
        );
    }

    public function testGetTaskListByTimeIntervalQueryBuilder()
    {
        $userId    = 123;
        $startDate = new \DateTime();
        $endDate   = clone $startDate;
        $endDate->add(new \DateInterval('P1D'));

        /** @var TaskRepository $repo */
        $repo = $this->em->getRepository('OroCRMTaskBundle:Task');
        $qb   = $repo->getTaskListByTimeIntervalQueryBuilder($userId, $startDate, $endDate);

        $this->assertEquals(
            'SELECT t.id, t.subject, t.description, t.dueDate, t.createdAt, t.updatedAt'
            . ' FROM OroCRM\Bundle\TaskBundle\Tests\Unit\Fixtures\Entity\Task t'
            . ' WHERE t.owner = :assignedTo AND t.dueDate >= :start AND t.dueDate <= :end',
            $qb->getDQL()
        );
        $this->assertEquals($userId, $qb->getParameter('assignedTo')->getValue());
        $this->assertEquals($startDate, $qb->getParameter('start')->getValue());
        $this->assertEquals($endDate, $qb->getParameter('end')->getValue());
    }

    public function testGetTaskListByTimeIntervaWithExtraFieldslQueryBuilder()
    {
        $userId    = 123;
        $startDate = new \DateTime();
        $endDate   = clone $startDate;
        $endDate->add(new \DateInterval('P1D'));
        $extraFields = ['status'];

        /** @var TaskRepository $repo */
        $repo = $this->em->getRepository('OroCRMTaskBundle:Task');
        $qb   = $repo->getTaskListByTimeIntervalQueryBuilder($userId, $startDate, $endDate, $extraFields);

        $this->assertEquals(
            'SELECT t.id, t.subject, t.description, t.dueDate, t.createdAt, t.updatedAt, t.status'
            . ' FROM OroCRM\Bundle\TaskBundle\Tests\Unit\Fixtures\Entity\Task t'
            . ' WHERE t.owner = :assignedTo AND t.dueDate >= :start AND t.dueDate <= :end',
            $qb->getDQL()
        );
        $this->assertEquals($userId, $qb->getParameter('assignedTo')->getValue());
        $this->assertEquals($startDate, $qb->getParameter('start')->getValue());
        $this->assertEquals($endDate, $qb->getParameter('end')->getValue());
    }
}
