<?php

namespace Oro\Bundle\ContactUsBundle\Tests\Functional\Fixtures;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Oro\Bundle\ContactUsBundle\Entity\ContactRequest;
use Oro\Bundle\TestFrameworkBundle\Tests\Functional\DataFixtures\LoadOrganization;

class LoadContactUsBundleFixtures extends AbstractFixture implements DependentFixtureInterface
{
    #[\Override]
    public function getDependencies(): array
    {
        return [LoadOrganization::class];
    }

    #[\Override]
    public function load(ObjectManager $manager): void
    {
        $contactUsRequest = new ContactRequest();
        $contactUsRequest->setFirstName('fname');
        $contactUsRequest->setLastName('lname');
        $contactUsRequest->setPhone('123123123');
        $contactUsRequest->setEmailAddress('email@email.com');
        $contactUsRequest->setComment('some comment');
        $contactUsRequest->setOwner($this->getReference(LoadOrganization::ORGANIZATION));
        $this->setReference('default_contact_us_request', $contactUsRequest);
        $manager->persist($contactUsRequest);
        $manager->flush();
    }
}
