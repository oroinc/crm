<?php

namespace Oro\Bundle\ActivityContactBundle\Tests\Functional\Command;

use Doctrine\ORM\EntityManagerInterface;
use Oro\Bundle\ActivityContactBundle\Command\ActivityContactRecalculateCommand;
use Oro\Bundle\ContactBundle\Entity\Contact;
use Oro\Bundle\ContactBundle\Tests\Functional\DataFixtures\LoadContactEntitiesData;
use Oro\Bundle\TestFrameworkBundle\Test\WebTestCase;

class ActivityContactRecalculateCommandTest extends WebTestCase
{
    protected function setUp(): void
    {
        $this->initClient();
        $this->loadFixtures([LoadContactEntitiesData::class]);
    }

    public function testRecalculationOfContactedContactHavingNoActivities()
    {
        $firstContact = $this->findContact(LoadContactEntitiesData::FIRST_ENTITY_NAME);

        $firstContact->setAcContactCount(1);
        $this->getEntityManager()->flush();
        $this->runActivityContactRecalculateCommand();
        $firstContact = $this->findContact(LoadContactEntitiesData::FIRST_ENTITY_NAME);

        $this->assertEquals(0, $firstContact->getAcContactCount());
    }

    private function runActivityContactRecalculateCommand()
    {
        $this->runCommand(ActivityContactRecalculateCommand::getDefaultName());
    }

    private function findContact(string $firstName): Contact
    {
        return self::getContainer()->get('doctrine')->getRepository(Contact::class)
            ->findOneBy(['firstName' => $firstName]);
    }

    private function getEntityManager(): EntityManagerInterface
    {
        return self::getContainer()->get('doctrine')->getManager();
    }
}
