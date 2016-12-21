<?php

namespace Oro\Bundle\ChannelBundle\Tests\Unit\Entity;

use Oro\Bundle\ChannelBundle\Entity\CustomerIdentity;

class CustomerIdentityTest extends AbstractEntityTestCase
{
    /** @var CustomerIdentity */
    protected $entity;

    /**
     * {@inheritDoc}
     */
    public function getEntityFQCN()
    {
        return 'Oro\Bundle\ChannelBundle\Entity\CustomerIdentity';
    }

    /**
     * {@inheritDoc}
     */
    public function getDataProvider()
    {
        $name    = 'Some name';
        $account = $this->createMock('Oro\Bundle\AccountBundle\Entity\Account');
        $contact = $this->createMock('Oro\Bundle\ContactBundle\Entity\Contact');
        $channel = $this->createMock('Oro\Bundle\ChannelBundle\Entity\Channel');
        $owner   = $this->createMock('Oro\Bundle\UserBundle\Entity\User');

        $someDateTime = new \DateTime();

        return [
            'name'        => ['name', $name, $name],
            'owner'       => ['owner', $owner, $owner],
            'account'     => ['account', $account, $account],
            'contact'     => ['contact', $contact, $contact],
            'dataChannel' => ['dataChannel', $channel, $channel],
            'createdAt'   => ['createdAt', $someDateTime, $someDateTime],
            'updatedAt'   => ['updatedAt', $someDateTime, $someDateTime]
        ];
    }

    public function testPrePersist()
    {
        $this->assertNull($this->entity->getCreatedAt());

        $this->entity->prePersist();

        $this->assertInstanceOf('DateTime', $this->entity->getCreatedAt());
        $this->assertLessThan(3, $this->entity->getCreatedAt()->diff(new \DateTime())->s);
    }

    public function testPreUpdate()
    {
        $this->assertNull($this->entity->getUpdatedAt());

        $this->entity->preUpdate();

        $this->assertInstanceOf('DateTime', $this->entity->getUpdatedAt());
        $this->assertLessThan(3, $this->entity->getUpdatedAt()->diff(new \DateTime())->s);
    }
}
