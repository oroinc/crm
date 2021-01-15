<?php

namespace Oro\Bundle\CaseBundle\Tests\Functional\DataFixtures;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Oro\Bundle\CaseBundle\Entity\CaseMailboxProcessSettings;
use Oro\Bundle\CaseBundle\Entity\CasePriority;
use Oro\Bundle\CaseBundle\Entity\CaseStatus;
use Oro\Bundle\EmailBundle\Entity\Mailbox;
use Oro\Bundle\TagBundle\Entity\Tagging;
use Oro\Bundle\TagBundle\Entity\TagManager;
use Oro\Bundle\UserBundle\Entity\User;
use Oro\Bundle\UserBundle\Tests\Functional\DataFixtures\LoadUserData;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

class LoadCaseMailboxSettingsData extends AbstractFixture implements ContainerAwareInterface, DependentFixtureInterface
{
    const PROCESS_SETTINGS = 'process_settings';

    /**
     * @var TagManager
     */
    private $tagManager;

    /**
     * {@inheritDoc}
     */
    public function setContainer(ContainerInterface $container = null)
    {
        $this->tagManager = $container->get('oro_tag.tag.manager');
    }

    /**
     * {@inheritDoc}
     */
    public function getDependencies()
    {
        return [
            LoadUserData::class,
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function load(ObjectManager $manager)
    {
        /** @var User $user */
        $user = $this->getReference(LoadUserData::SIMPLE_USER);
        $organization = $user->getOrganization();

        $processSettings = new CaseMailboxProcessSettings();
        $processSettings->setOwner($user);

        $status = $manager->getRepository(CaseStatus::class)->find(CasePriority::PRIORITY_LOW);
        $processSettings->setStatus($status);

        $priority = $manager->getRepository(CasePriority::class)->find(CasePriority::PRIORITY_LOW);
        $processSettings->setPriority($priority);

        $mailbox = new Mailbox();
        $mailbox->setLabel('Test Mailbox')
            ->setEmail('test@example.org')
            ->setProcessSettings($processSettings);

        $this->addReference(self::PROCESS_SETTINGS, $processSettings);
        $manager->persist($processSettings);
        $manager->persist($mailbox);

        // TagManager requires entity that has id.
        $manager->flush();

        $tag = $this->tagManager->loadOrCreateTag('My tag', $organization);
        $tag->setOrganization($organization);
        $manager->persist($tag);
        $tags = new ArrayCollection([$tag]);
        $this->tagManager->setTags($processSettings, $tags);
        $tagging = new Tagging($tag, $processSettings);
        $manager->persist($tagging);

        $manager->flush();
    }
}
