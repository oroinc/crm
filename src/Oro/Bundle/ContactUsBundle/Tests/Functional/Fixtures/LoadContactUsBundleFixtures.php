<?php

namespace Oro\Bundle\ContactUsBundle\Tests\Functional\Fixtures;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Persistence\ObjectManager;
use Oro\Bundle\ContactUsBundle\Entity\ContactRequest;

class LoadContactUsBundleFixtures extends AbstractFixture
{
    const CHANNEL_TYPE = 'custom';
    const CHANNEL_NAME = 'custom Channel';

    /** @var ObjectManager */
    protected $em;

    /**
     * {@inheritDoc}
     */
    public function load(ObjectManager $manager)
    {
        $this->em = $manager;
        $organization = $manager->getRepository('OroOrganizationBundle:Organization')->getFirst();

        $contactUsRequest = new ContactRequest();
        $contactUsRequest->setFirstName('fname');
        $contactUsRequest->setLastName('lname');
        $contactUsRequest->setPhone('123123123');
        $contactUsRequest->setEmailAddress('email@email.com');
        $contactUsRequest->setComment('some comment');
        $contactUsRequest->setOwner($organization);

        $this->em->persist($contactUsRequest);
        $this->em->flush();

        $this->setReference('default_contact_us_request', $contactUsRequest);
    }
}
