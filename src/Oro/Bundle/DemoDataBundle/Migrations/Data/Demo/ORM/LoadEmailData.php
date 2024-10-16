<?php

namespace Oro\Bundle\DemoDataBundle\Migrations\Data\Demo\ORM;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Oro\Bundle\ContactBundle\Entity\Contact;
use Oro\Bundle\EmailBundle\Builder\EmailEntityBuilder;
use Oro\Bundle\EmailBundle\Entity\EmailOrigin;
use Oro\Bundle\EmailBundle\Entity\EmailThread;
use Oro\Bundle\EmailBundle\Entity\EmailUser;
use Oro\Bundle\EmailBundle\Model\FolderType;
use Oro\Bundle\SecurityBundle\Authentication\Token\UsernamePasswordOrganizationToken;
use Oro\Bundle\UserBundle\Entity\User;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerAwareTrait;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

/**
 * Loads new Email entities.
 */
class LoadEmailData extends AbstractFixture implements DependentFixtureInterface, ContainerAwareInterface
{
    use ContainerAwareTrait;

    #[\Override]
    public function getDependencies(): array
    {
        return [LoadContactData::class];
    }

    #[\Override]
    public function load(ObjectManager $manager): void
    {
        $templates = $this->loadEmailTemplates();

        $tokenStorage = $this->container->get('security.token_storage');
        $this->loadEmailsDemo($manager, $tokenStorage, $templates);
        $tokenStorage->setToken(null);
    }

    private function loadEmailTemplates(): array
    {
        $templates = [];
        $dictionaryDir = $this->container
            ->get('kernel')
            ->locateResource('@OroDemoDataBundle/Migrations/Data/Demo/ORM/dictionaries');
        $handle = fopen($dictionaryDir . DIRECTORY_SEPARATOR. 'emails.csv', 'r');
        if ($handle) {
            $headers = [];
            if (($data = fgetcsv($handle, 1000, ',')) !== false) {
                //read headers
                $headers = $data;
            }
            while (($data = fgetcsv($handle, 1000, ',')) !== false) {
                $templates[] = array_combine($headers, array_values($data));
            }
        }

        return $templates;
    }

    private function loadEmailsDemo(
        ObjectManager $manager,
        TokenStorageInterface $tokenStorage,
        array $templates
    ): void {
        $emailEntityBuilder = $this->container->get('oro_email.email.entity.builder');
        $emailOriginHelper = $this->container->get('oro_email.tools.email_origin_helper');
        $contacts = $manager->getRepository(Contact::class)->findAll();
        $contactCount = \count($contacts);

        for ($i = 0; $i < 50; ++$i) {
            $contactRandom = rand(0, $contactCount - 1);

            /** @var Contact $contact */
            $contact = $contacts[$contactRandom];
            $owner = $contact->getOwner();
            $origin = $emailOriginHelper->getEmailOrigin($owner->getEmail());
            $randomTemplate = array_rand($templates);

            $emailUser = $this->addEmailUser(
                $tokenStorage,
                $emailEntityBuilder,
                $templates,
                $randomTemplate,
                $owner,
                $contact,
                $origin
            );
            if ($i % 7 == 0) {
                $thread = new EmailThread();
                $manager->persist($thread);
                $emailUser->getEmail()->setThread($thread);
                $randomNumber = rand(1, 7);
                for ($j = 0; $j < $randomNumber; ++$j) {
                    $eu = $this->addEmailUser(
                        $tokenStorage,
                        $emailEntityBuilder,
                        $templates,
                        $randomTemplate,
                        $owner,
                        $contact,
                        $origin
                    );
                    $eu->getEmail()->setSubject('Re: ' . $emailUser->getEmail()->getSubject());
                    $eu->getEmail()->setThread($thread);
                    $eu->getEmail()->setHead(false);
                }
            }

            $emailEntityBuilder->getBatch()->persist($manager);
        }
        $manager->flush();
    }

    private function addEmailUser(
        TokenStorageInterface $tokenStorage,
        EmailEntityBuilder $emailEntityBuilder,
        array $templates,
        string $randomTemplate,
        User $owner,
        Contact $contact,
        EmailOrigin $origin
    ): EmailUser {
        $ownerEmail = $owner->getFullName() . ' <' . $owner->getEmail() . '>';
        $contactEmail
            = $contact->getFirstName() . ' ' . $contact->getLastName()
            . ' <' . $contact->getPrimaryEmail()->getEmail() . '>';
        $emailUser = $emailEntityBuilder->emailUser(
            $templates[$randomTemplate]['Subject'],
            $ownerEmail,
            $contactEmail,
            new \DateTime('now'),
            new \DateTime('now'),
            new \DateTime('now')
        );

        $tokenStorage->setToken(new UsernamePasswordOrganizationToken(
            $owner,
            'main',
            $this->getReference('default_organization'),
            $owner->getUserRoles()
        ));

        $emailUser->addFolder($origin->getFolder(FolderType::SENT));
        $emailUser->setOrigin($origin);
        $emailUser->setOwner($owner);
        $emailUser->setOrganization($owner->getOrganization());

        $emailBody = $emailEntityBuilder->body(
            "Hi,\n" . $templates[$randomTemplate]['Text'],
            false,
            true
        );
        $emailUser->getEmail()->setEmailBody($emailBody);
        $emailUser->getEmail()->setMessageId(sprintf('<id.%s@%s', uniqid(), '@bap.migration.generated>'));

        return $emailUser;
    }
}
