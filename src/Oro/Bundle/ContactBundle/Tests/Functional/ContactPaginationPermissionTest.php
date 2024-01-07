<?php

namespace Oro\Bundle\ContactBundle\Tests\Functional;

use Oro\Bundle\ContactBundle\Entity\Contact;
use Oro\Bundle\ContactBundle\Tests\Functional\DataFixtures\LoadContactEntitiesData;
use Oro\Bundle\TestFrameworkBundle\Tests\Functional\DataFixtures\LoadUser;

class ContactPaginationPermissionTest extends AbstractContactPaginationTestCase
{
    public function testViewChangePermissions(): void
    {
        $this->client->followRedirects();

        // open first contact prepare entity pagination set
        $crawler = $this->openEntity(
            'oro_contact_view',
            LoadContactEntitiesData::FIRST_ENTITY_NAME,
            $this->gridParams
        );

        // check all pagination links and return to first
        $this->checkPaginationLinks($crawler);

        // change owner to second contact
        $this->loadFixtures([LoadUser::class]);
        $em = self::getContainer()->get('doctrine')->getManager();
        $contact = $this->getContainer()->get('doctrine')->getRepository(Contact::class)
            ->findOneBy(['firstName' => LoadContactEntitiesData::SECOND_ENTITY_NAME]);
        $contact->setOwner($this->getReference(LoadUser::USER));
        $em->flush();

        // click next link
        $next = $crawler->filter('#entity-pagination a .fa-chevron-right')->ancestors()->link();
        $this->client->click($next);
        $crawler = $this->redirectViaFrontend(
            'You do not have sufficient permissions to access records. You are now viewing 3 records.'
        );

        $this->assertCurrentContactName($crawler, LoadContactEntitiesData::THIRD_ENTITY_NAME);
        $this->assertPositionEntity($crawler, 2, 3);
    }
}
