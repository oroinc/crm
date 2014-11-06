<?php

namespace OroCRM\Bundle\TaskBundle\Tests\Unit\Entity\Repository;

use Doctrine\Common\Annotations\AnnotationReader;
use Doctrine\ORM\Mapping\Driver\AnnotationDriver;

use Oro\Bundle\TestFrameworkBundle\Test\Doctrine\ORM\OrmTestCase;
use Oro\Bundle\TestFrameworkBundle\Test\Doctrine\ORM\Mocks\EntityManagerMock;
use OroCRM\Bundle\TaskBundle\Entity\Repository\TaskRepository;

class TaskRepositoryTest extends OrmTestCase
{
    /**
     * @var EntityManagerMock
     */
    protected $em;

    protected function setUp()
    {
        $reader         = new AnnotationReader();
        $metadataDriver = new AnnotationDriver(
            $reader,
            'OroCRM\Bundle\TaskBundle\Entity'
        );

        $this->em = $this->getTestEntityManager();
        $this->em->getConfiguration()->setMetadataDriverImpl($metadataDriver);
        $this->em->getConfiguration()->setEntityNamespaces(
            array(
                'OroCRMTaskBundle' => 'OroCRM\Bundle\TaskBundle\Entity'
            )
        );
    }

    public function testGetTaskListByTimeIntervalQueryBuilder()
    {
        /** @var TaskRepository $repo */
        $repo = $this->em->getRepository('OroCRMTaskBundle:Task');

        $qb = $repo->getTaskListByTimeIntervalQueryBuilder(1, new \DateTime(), new \DateTime());

        $this->assertEquals(
            'SELECT t.id, t.subject, t.description, t.dueDate, t.createdAt, t.updatedAt '
            . 'FROM OroCRM\Bundle\TaskBundle\Entity\Task t INNER JOIN t.workflowStep step '
            . 'WHERE (t.owner = :assignedTo AND step.name != :step) AND (t.dueDate >= :start AND t.dueDate <= :end)',
            $qb->getDQL());
    }


}
