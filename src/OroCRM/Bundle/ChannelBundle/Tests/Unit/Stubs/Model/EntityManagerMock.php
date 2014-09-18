<?php

namespace OroCRM\Bundle\ChannelBundle\Tests\Unit\Stubs\Model;

use Doctrine\ORM\ORMInvalidArgumentException;

use Oro\Bundle\TestFrameworkBundle\Test\Doctrine\ORM\Mocks\EntityManagerMock as BaseMock;

class EntityManagerMock extends BaseMock
{
    public function flush($entity = null)
    {
        $this->getUnitOfWork()->commit($entity);
    }

    public function persist($entity)
    {
        if (!is_object($entity)) {
            throw ORMInvalidArgumentException::invalidObject('EntityManager#persist()', $entity);
        }

        $this->getUnitOfWork()->persist($entity);
    }
}
