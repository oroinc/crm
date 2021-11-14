<?php

namespace Oro\Bundle\CaseBundle\Tests\Unit\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Oro\Bundle\CaseBundle\Entity\CaseMailboxProcessSettings;
use Oro\Bundle\CaseBundle\Entity\CasePriority;
use Oro\Bundle\CaseBundle\Entity\CaseStatus;
use Oro\Bundle\UserBundle\Entity\User;
use Oro\Component\Testing\Unit\EntityTestCaseTrait;

class CaseMailboxProcessSettingsTest extends \PHPUnit\Framework\TestCase
{
    use EntityTestCaseTrait;

    /**
     * Test setters getters
     */
    public function testAccessors()
    {
        $entity = new CaseMailboxProcessSettings();

        self::assertInstanceOf(ArrayCollection::class, $entity->getTags());

        $this->assertPropertyAccessors(
            $entity,
            [
                ['owner', $this->createMock(User::class)],
                ['assignTo', $this->createMock(User::class)],
                ['priority', $this->createMock(CasePriority::class)],
                ['status', $this->createMock(CaseStatus::class)],
                ['tags', $this->createMock(ArrayCollection::class), false],
            ]
        );
    }
}
