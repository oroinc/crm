<?php

namespace Oro\Bundle\DemoDataBundle\Migrations\Data\Demo\ORM;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityRepository;
use Doctrine\Persistence\ObjectManager;
use Oro\Bundle\OrganizationBundle\Entity\Organization;
use Oro\Bundle\SecurityBundle\Authentication\Token\UsernamePasswordOrganizationToken;
use Oro\Bundle\TagBundle\Entity\Tag;
use Oro\Bundle\TagBundle\Entity\TagManager;
use Oro\Bundle\UserBundle\Entity\User;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Loads new Tag entities.
 */
class LoadTagsData extends AbstractFixture implements ContainerAwareInterface, DependentFixtureInterface
{
    /** @var ContainerInterface */
    protected $container;

    /** @var EntityRepository */
    protected $accountsRepository;

    /** @var EntityRepository */
    protected $usersRepository;

    /* @var EntityRepository */
    protected $contactsRepository;

    /** @var TagManager */
    protected $tagManager;

    /** @var Tag[] */
    protected $tagsUser;

    /** @var Tag[] */
    protected $tagsAccount;

    /** @var EntityManager */
    protected $em;

    /** @var Organization */
    protected $organization;

    /**
     * {@inheritdoc}
     */
    public function getDependencies()
    {
        return [
            'Oro\Bundle\DemoDataBundle\Migrations\Data\Demo\ORM\LoadUsersData',
            'Oro\Bundle\DemoDataBundle\Migrations\Data\Demo\ORM\LoadAccountData',
            'Oro\Bundle\DemoDataBundle\Migrations\Data\Demo\ORM\LoadContactData'
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function setContainer(ContainerInterface $container = null)
    {
        $this->container = $container;
    }

    /**
     * {@inheritdoc}
     */
    public function load(ObjectManager $manager)
    {
        $this->initSupportingEntities($manager);
        $this->loadUsersTags();
        $this->loadAccountsTags();
        $this->loadContactsTags();

        $tokenStorage = $this->container->get('security.token_storage');
        $tokenStorage->setToken(null);
    }

    /**
     * @param ObjectManager $manager
     */
    protected function initSupportingEntities(ObjectManager $manager = null)
    {
        if ($manager) {
            $this->em = $manager;
        } else {
            $this->em = $this->container->get('doctrine.orm.entity_manager');
        }

        $this->usersRepository    = $this->em->getRepository('OroUserBundle:User')->findAll();
        $this->accountsRepository = $this->em->getRepository('OroAccountBundle:Account')->findAll();
        $this->contactsRepository = $this->em->getRepository('OroContactBundle:Contact')->findAll();

        $this->tagManager   = $this->container->get('oro_tag.tag.manager');
        $this->organization = $this->getReference('default_organization');

        /** @var User $adminUser */
        $adminUser = $this->em->getRepository('OroUserBundle:User')
            ->createQueryBuilder('u')
            ->select('u')
            ->orderBy('u.id')
            ->getQuery()
            ->setMaxResults(1)
            ->getSingleResult();
        $token        = new UsernamePasswordOrganizationToken(
            $adminUser,
            $adminUser->getUsername(),
            'main',
            $this->organization,
            $adminUser->getUserRoles()
        );
        $tokenStorage = $this->container->get('security.token_storage');
        $tokenStorage->setToken($token);
    }

    /**
     * @param $tagsNames
     *
     * @return Tag[]
     */
    protected function createTags($tagsNames)
    {
        $tags = [];
        foreach ($tagsNames as $tagName) {
            $tag = new Tag($tagName);
            $tag->setOrganization($this->organization);
            $tags[] = $tag;
        }

        return $tags;
    }

    public function loadUsersTags()
    {
        $this->tagsUser = $this->createTags(['Friends', 'Developer', 'Wholesale']);
        $userTagsCount  = count($this->tagsUser);

        foreach ($this->usersRepository as $user) {
            $this->tagManager->setTags(
                $user,
                new ArrayCollection(
                    [
                        $this->tagsUser[rand(0, $userTagsCount - 1)]
                    ]
                )
            );
            $this->tagManager->saveTagging($user, false);
        }
        $this->flush($this->em);
    }

    public function loadAccountsTags()
    {
        $this->tagsAccount = $this->createTags(
            [
                'Commercial',
                'Business',
                'Vendor',
                'Gold Partner',
                'Service',
                '#new',
                '#vip',
                '#popular',
                '#call',
                '#discontinued',
                'Premium'
            ]
        );
        $userTagsCount     = count($this->tagsUser);
        $accountTagsCount  = count($this->tagsAccount);

        foreach ($this->accountsRepository as $account) {
            $this->tagManager->setTags(
                $account,
                new ArrayCollection(
                    [
                        $this->tagsUser[rand(0, $userTagsCount - 1)],
                        $this->tagsAccount[rand(0, $accountTagsCount - 1)]
                    ]
                )
            );
            $this->tagManager->saveTagging($account, false);
        }
        $this->flush($this->em);
    }

    public function loadContactsTags()
    {
        $userTagsCount    = count($this->tagsUser);
        $accountTagsCount = count($this->tagsAccount);

        foreach ($this->contactsRepository as $contact) {
            $this->tagManager->setTags(
                $contact,
                new ArrayCollection(
                    [
                        $this->tagsUser[rand(0, $userTagsCount - 1)],
                        $this->tagsAccount[rand(0, $accountTagsCount - 1)]
                    ]
                )
            );

            $this->tagManager->saveTagging($contact, false);
        }
        $this->flush($this->em);
    }

    /**
     * Flush objects
     *
     * @param mixed $manager
     */
    private function flush($manager)
    {
        $manager->flush();
    }
}
