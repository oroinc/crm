<?php

namespace OroCRM\Bundle\DemoDataBundle\Migrations\Data\Demo\ORM;

use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;

use Oro\Bundle\EmailBundle\Model\FolderType;
use Oro\Bundle\EmailBundle\Tools\EmailOriginHelper;
use Oro\Bundle\EmailBundle\Builder\EmailEntityBuilder;
use Oro\Bundle\EmailBundle\Entity\EmailOrigin;
use Oro\Bundle\EmailBundle\Entity\EmailThread;
use Oro\Bundle\EmailBundle\Entity\EmailUser;
use Oro\Bundle\SecurityBundle\Authentication\Token\UsernamePasswordOrganizationToken;
use Oro\Bundle\UserBundle\Entity\User;

use OroCRM\Bundle\ContactBundle\Entity\Contact;

class LoadEmailData extends AbstractFixture implements DependentFixtureInterface, ContainerAwareInterface
{
    /**
     * @var string
     */
    protected $templates;

    /**
     * @var EmailEntityBuilder
     */
    protected $emailEntityBuilder;

    /**
     * @var EmailOriginHelper
     */
    protected $emailOriginHelper;

    /**
     * @var ContainerInterface
     */
    protected $container;

    /**
     * {@inheritdoc}
     */
    public function getDependencies()
    {
        return ['OroCRM\Bundle\DemoDataBundle\Migrations\Data\Demo\ORM\LoadContactData',];
    }

    /**
     * {@inheritdoc}
     */
    public function setContainer(ContainerInterface $container = null)
    {
        if (!$container) {
            return;
        }

        $this->container = $container;
        $this->emailEntityBuilder = $container->get('oro_email.email.entity.builder');
        $this->emailOriginHelper = $container->get('oro_email.tools.email_origin_helper');
    }

    /**
     * {@inheritDoc}
     */
    public function load(ObjectManager $om)
    {
        $this->loadEmailTemplates();
        $this->loadEmailsDemo($om);
        $om->flush();
    }

    protected function loadEmailTemplates()
    {
        $dictionaryDir = $this->container
            ->get('kernel')
            ->locateResource('@OroCRMDemoDataBundle/Migrations/Data/Demo/ORM/dictionaries');

        $handle = fopen($dictionaryDir . DIRECTORY_SEPARATOR. "emails.csv", "r");
        if ($handle) {
            $headers = [];
            if (($data = fgetcsv($handle, 1000, ",")) !== false) {
                //read headers
                $headers = $data;
            }
            while (($data = fgetcsv($handle, 1000, ",")) !== false) {
                $this->templates[] = array_combine($headers, array_values($data));
            }
        }
    }

    /**
     * @param ObjectManager $om
     */
    protected function loadEmailsDemo(ObjectManager $om)
    {
        $contacts = $om->getRepository('OroCRMContactBundle:Contact')->findAll();
        $contactCount = count($contacts);

        for ($i = 0; $i < 100; ++$i) {
            $contactRandom = rand(0, $contactCount - 1);

            /** @var Contact $contact */
            $contact = $contacts[$contactRandom];
            $owner = $contact->getOwner();
            $origin = $this->emailOriginHelper->getEmailOrigin($owner->getEmail());
            $randomTemplate = array_rand($this->templates);

            $emailUser = $this->addEmailUser($randomTemplate, $owner, $contact, $origin);
            if ($i % 7 == 0) {
                $thread = new EmailThread();
                $om->persist($thread);
                $emailUser->getEmail()->setThread($thread);
                $randomNumber = rand(1, 7);
                for ($j = 0; $j < $randomNumber; ++$j) {
                    $eu = $this->addEmailUser($randomTemplate, $owner, $contact, $origin);
                    $eu->getEmail()->setSubject('Re: ' . $emailUser->getEmail()->getSubject());
                    $eu->getEmail()->setThread($thread);
                    $eu->getEmail()->setHead(false);
                }
            }

            $this->emailEntityBuilder->getBatch()->persist($om);
        }
    }

    /**
     * @param User $user
     */
    protected function setSecurityContext($user)
    {
        $securityContext = $this->container->get('security.context');
        $token = new UsernamePasswordOrganizationToken(
            $user,
            $user->getUsername(),
            'main',
            $this->getReference('default_organization')
        );
        $securityContext->setToken($token);
    }

    /**
     * @param $randomTemplate
     * @param User $owner
     * @param Contact $contact
     * @param EmailOrigin $origin
     *
     * @return EmailUser
     */
    protected function addEmailUser($randomTemplate, $owner, $contact, $origin)
    {
        $ownerEmail = $owner->getFullName() . ' <' . $owner->getEmail() . '>';
        $contactEmail
            = $contact->getFirstName() . ' ' . $contact->getLastName()
            . ' <' . $contact->getPrimaryEmail()->getEmail() . '>';
        $emailUser = $this->emailEntityBuilder->emailUser(
            $this->templates[$randomTemplate]['Subject'],
            $ownerEmail,
            $contactEmail,
            new \DateTime('now'),
            new \DateTime('now'),
            new \DateTime('now')
        );

        $this->setSecurityContext($owner);

        $emailUser->addFolder($origin->getFolder(FolderType::SENT));
        $emailUser->setOrigin($origin);
        $emailUser->setOwner($owner);
        $emailUser->setOrganization($owner->getOrganization());

        $emailBody = $this->emailEntityBuilder->body(
            "Hi,\n" . $this->templates[$randomTemplate]['Text'],
            false,
            true
        );
        $emailUser->getEmail()->setEmailBody($emailBody);
        $emailUser->getEmail()->setMessageId(sprintf('<id.%s@%s', uniqid(), '@bap.migration.generated>'));

        return $emailUser;
    }
}
