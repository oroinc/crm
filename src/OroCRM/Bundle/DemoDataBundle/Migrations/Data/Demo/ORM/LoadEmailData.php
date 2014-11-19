<?php

namespace OroCRM\Bundle\DemoDataBundle\Migrations\Data\Demo\ORM;

use Oro\Bundle\SecurityBundle\Authentication\Token\UsernamePasswordOrganizationToken;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;

use Oro\Bundle\EmailBundle\Model\FolderType;
use Oro\Bundle\EmailBundle\Mailer\Processor;
use Oro\Bundle\EmailBundle\Builder\EmailEntityBuilder;

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
     * @var Processor
     */
    protected $mailerProcessor;

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
        $this->mailerProcessor = $container->get('oro_email.mailer.processor');
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
            $origin = $this->mailerProcessor->getEmailOrigin($owner->getEmail());

            $randomTemplate = array_rand($this->templates);

            $email = $this->emailEntityBuilder->email(
                $this->templates[$randomTemplate]['Subject'],
                $owner->getEmail(),
                $contact->getPrimaryEmail()->getEmail(),
                new \DateTime('now'),
                new \DateTime('now'),
                new \DateTime('now')
            );

            $this->setSecurityContext($owner);
            $email->addFolder($origin->getFolder(FolderType::SENT));

            $emailBody = $this->emailEntityBuilder->body(
                "Hi,\n" . $this->templates[$randomTemplate]['Text'],
                false,
                true
            );
            $email->setEmailBody($emailBody);
            $email->setMessageId(sprintf('id.%s@%s', uniqid(), '@bap.migration.generated'));

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
}
