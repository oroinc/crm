<?php

namespace OroCRM\Bundle\ActivityContactBundle\Tests\Functional\EventListener;

use Doctrine\Bundle\DoctrineBundle\Registry;
use Doctrine\ORM\EntityManager;

use Oro\Bundle\ActivityBundle\Manager\ActivityManager;
use Oro\Bundle\TestFrameworkBundle\Test\WebTestCase;

use OroCRM\Bundle\ActivityContactBundle\Direction\DirectionProviderInterface;
use OroCRM\Bundle\CallBundle\Entity\Call;
use OroCRM\Bundle\CallBundle\Entity\CallDirection;
use OroCRM\Bundle\ContactBundle\Entity\Contact;
use OroCRM\Bundle\ContactBundle\Tests\Functional\DataFixtures\LoadContactEntitiesData;

/**
 * @outputBuffering enabled
 * @dbIsolation
 */
class ActivityListenerTest extends WebTestCase
{
    public function setUp()
    {
        $this->initClient();
        $this->loadFixtures([
            'OroCRM\Bundle\ContactBundle\Tests\Functional\DataFixtures\LoadContactEntitiesData',
        ]);
    }

    public function testRemoveContactFromContextShouldDecreaseActivityCounter()
    {
        $firstContact = $this->findContact(LoadContactEntitiesData::FIRST_ENTITY_NAME);
        $secondContact = $this->findContact(LoadContactEntitiesData::SECOND_ENTITY_NAME);

        $firstContacted = $firstContact->getAcContactCount();
        $secondContacted = $firstContact->getAcContactCount();

        $call = new Call();
        $call
            ->setPhoneNumber('3058304958')
            ->setSubject('subj')
            ->setDirection($this->findCallDirection(DirectionProviderInterface::DIRECTION_OUTGOING));
        $this->getActivityManager()->addActivityTargets($call, [$firstContact, $secondContact]);
        $this->getEntityManager()->persist($call);
        $this->getEntityManager()->flush();

        $this->assertEquals($firstContacted + 1, $firstContact->getAcContactCount());
        $this->assertEquals($secondContacted + 1, $secondContact->getAcContactCount());

        $this->getActivityManager()->removeActivityTarget($call, $secondContact);
        $this->getEntityManager()->flush();

        $this->assertEquals($firstContacted + 1, $firstContact->getAcContactCount());
        $this->assertEquals($secondContacted, $secondContact->getAcContactCount());
    }

    /**
     * @return ActivityManager
     */
    protected function getActivityManager()
    {
        return $this->getContainer()->get('oro_activity.manager');
    }

    /**
     * @param string $name
     *
     * @return CallDirection
     */
    protected function findCallDirection($name)
    {
        return $this->getRegistry()->getRepository('OroCRMCallBundle:CallDirection')->findOneByName($name);
    }

    /**
     * @param string $firstName
     *
     * @return Contact
     */
    protected function findContact($firstName)
    {
        return $this->getRegistry()->getRepository('OroCRMContactBundle:Contact')->findOneByFirstName($firstName);
    }

    /**
     * @return EntityManager
     */
    protected function getEntityManager()
    {
        return $this->getRegistry()->getManager();
    }

    /**
     * @return Registry
     */
    protected function getRegistry()
    {
        return $this->getContainer()->get('doctrine');
    }
}
