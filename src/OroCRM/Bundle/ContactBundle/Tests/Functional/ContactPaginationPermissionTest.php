<?php

namespace OroCRM\Bundle\ContactBundle\Tests\Functional;

use OroCRM\Bundle\ContactBundle\Tests\Functional\DataFixtures\LoadContactEntitiesData;
use OroCRM\Bundle\ContactBundle\Entity\Contact;

/**
 * @outputBuffering enabled
 * @dbIsolation
 */
class ContactPaginationPermissionTest extends AbstractContactPaginationTestCase
{
    public function testViewChangePermissions()
    {
        $this->client->followRedirects(true);

        // open first contact prepare entity pagination set
        $crawler = $this->openEntity(
            'orocrm_contact_view',
            LoadContactEntitiesData::FIRST_ENTITY_NAME,
            $this->gridParams
        );

        // check all pagination links and return to first
        $this->checkPaginationLinks($crawler);

        // change owner to second contact
        $em = $this->getContainer()->get('doctrine.orm.default_entity_manager');
        $contact = $this->getContainer()->get('doctrine')
            ->getRepository('OroCRMContactBundle:Contact')
            ->findOneBy(['firstName' => LoadContactEntitiesData::SECOND_ENTITY_NAME]);

        $admin = $this->getContainer()
            ->get('doctrine')
            ->getRepository('OroUserBundle:User')
            ->findOneByUsername('admin');

        $contact->setOwner($admin);
        $em->flush();

        // click next link
        $next = $crawler->filter('#entity-pagination a .icon-chevron-right')->parents()->link();
        $this->client->click($next);
        $crawler = $this->redirectViaFrontend(
            'You do not have sufficient permissions to access records. You are now viewing 3 records.'
        );

        $this->assertCurrentContactName($crawler, LoadContactEntitiesData::THIRD_ENTITY_NAME);
        $this->assertPositionEntity($crawler, 2, 3);
    }
}
