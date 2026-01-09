<?php

namespace Oro\Bundle\DemoDataBundle\Migrations\Data\Demo\ORM;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Oro\Bundle\AccountBundle\Entity\Account;
use Oro\Bundle\ContactBundle\Entity\Contact;
use Oro\Bundle\OrganizationBundle\Entity\Organization;
use Oro\Bundle\SecurityBundle\Authentication\Token\UsernamePasswordOrganizationToken;
use Oro\Bundle\TagBundle\Entity\Tag;
use Oro\Bundle\TagBundle\Entity\TagManager;
use Oro\Bundle\UserBundle\Entity\User;
use Oro\Component\DependencyInjection\ContainerAwareInterface;
use Oro\Component\DependencyInjection\ContainerAwareTrait;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;

/**
 * Loads new Tag entities.
 */
class LoadTagsData extends AbstractFixture implements ContainerAwareInterface, DependentFixtureInterface
{
    use ContainerAwareTrait;

    #[\Override]
    public function getDependencies(): array
    {
        return [
            LoadUsersData::class,
            LoadAccountData::class,
            LoadContactData::class
        ];
    }

    #[\Override]
    public function load(ObjectManager $manager): void
    {
        $organization = $this->getReference('default_organization');
        $tokenStorage = $this->container->get('security.token_storage');
        $tokenStorage->setToken($this->getUserToken($manager, $organization));

        $tagManager = $this->container->get('oro_tag.tag.manager');
        $userTags = $this->loadUsersTags($manager, $tagManager, $organization);
        $accountTags = $this->loadAccountsTags($manager, $tagManager, $organization, $userTags);
        $this->loadContactsTags($manager, $tagManager, $userTags, $accountTags);

        $tokenStorage->setToken(null);
    }

    private function getUserToken(ObjectManager $manager, Organization $organization): TokenInterface
    {
        /** @var User $adminUser */
        $adminUser = $manager->getRepository(User::class)
            ->createQueryBuilder('u')
            ->select('u')
            ->orderBy('u.id')
            ->getQuery()
            ->setMaxResults(1)
            ->getSingleResult();

        return new UsernamePasswordOrganizationToken(
            $adminUser,
            'main',
            $organization,
            $adminUser->getUserRoles()
        );
    }

    /**
     * @param string[] $tagsNames
     *
     * @return Tag[]
     */
    private function createTags(array $tagsNames, Organization $organization): array
    {
        $tags = [];
        foreach ($tagsNames as $tagName) {
            $tag = new Tag($tagName);
            $tag->setOrganization($organization);
            $tags[] = $tag;
        }

        return $tags;
    }

    private function loadUsersTags(ObjectManager $manager, TagManager $tagManager, Organization $organization): array
    {
        $userTags = $this->createTags(['Friends', 'Developer', 'Wholesale'], $organization);
        $userTagsCount = \count($userTags);

        $users = $manager->getRepository(User::class)->findAll();
        foreach ($users as $user) {
            $tagManager->setTags(
                $user,
                new ArrayCollection([
                    $userTags[rand(0, $userTagsCount - 1)]
                ])
            );
            $tagManager->saveTagging($user, false);
        }
        $manager->flush();

        return $userTags;
    }

    private function loadAccountsTags(
        ObjectManager $manager,
        TagManager $tagManager,
        Organization $organization,
        array $userTags
    ): array {
        $accountTags = $this->createTags(
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
            ],
            $organization
        );
        $userTagsCount = \count($userTags);
        $accountTagsCount = \count($accountTags);

        $accounts = $manager->getRepository(Account::class)->findAll();
        foreach ($accounts as $account) {
            $tagManager->setTags(
                $account,
                new ArrayCollection([
                    $userTags[rand(0, $userTagsCount - 1)],
                    $accountTags[rand(0, $accountTagsCount - 1)]
                ])
            );
            $tagManager->saveTagging($account, false);
        }
        $manager->flush();

        return $accountTags;
    }

    private function loadContactsTags(
        ObjectManager $manager,
        TagManager $tagManager,
        array $userTags,
        array $accountTags
    ): void {
        $userTagsCount = \count($userTags);
        $accountTagsCount = \count($accountTags);

        $contacts = $manager->getRepository(Contact::class)->findAll();
        foreach ($contacts as $contact) {
            $tagManager->setTags(
                $contact,
                new ArrayCollection([
                    $userTags[rand(0, $userTagsCount - 1)],
                    $accountTags[rand(0, $accountTagsCount - 1)]
                ])
            );

            $tagManager->saveTagging($contact, false);
        }
        $manager->flush();
    }
}
