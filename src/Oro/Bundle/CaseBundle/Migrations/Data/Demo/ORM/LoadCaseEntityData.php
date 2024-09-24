<?php

namespace Oro\Bundle\CaseBundle\Migrations\Data\Demo\ORM;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Oro\Bundle\AccountBundle\Entity\Account;
use Oro\Bundle\CaseBundle\Entity\CaseComment;
use Oro\Bundle\CaseBundle\Entity\CaseEntity;
use Oro\Bundle\CaseBundle\Entity\CasePriority;
use Oro\Bundle\CaseBundle\Entity\CaseSource;
use Oro\Bundle\CaseBundle\Entity\CaseStatus;
use Oro\Bundle\CaseBundle\Model\CaseEntityManager;
use Oro\Bundle\ContactBundle\Entity\Contact;
use Oro\Bundle\DemoDataBundle\Migrations\Data\Demo\ORM\LoadAccountData;
use Oro\Bundle\DemoDataBundle\Migrations\Data\Demo\ORM\LoadContactData;
use Oro\Bundle\DemoDataBundle\Migrations\Data\Demo\ORM\LoadUsersData;
use Oro\Bundle\UserBundle\Entity\User;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerAwareTrait;

/**
 * Loads new Case entities.
 */
class LoadCaseEntityData extends AbstractFixture implements DependentFixtureInterface, ContainerAwareInterface
{
    use ContainerAwareTrait;

    public const CASES_COUNT = 20;
    private const MIN_COMMENTS_PER_CASE = 0;
    private const MAX_COMMENTS_PER_CASE = 20;

    private static array $fixtureSubjects = [
        'Lorem ipsum dolor sit amet, consectetuer adipiscing elit',
        'Aenean commodo ligula eget dolor',
        'Aenean massa',
        'Cum sociis natoque penatibus et magnis dis parturient montes, nascetur ridiculus mus',
        'Donec quam felis, ultricies nec, pellentesque eu, pretium quis, sem',
        'Nulla consequat massa quis enim',
        'Donec pede justo, fringilla vel, aliquet nec, vulputate eget, arcu',
        'In enim justo, rhoncus ut, imperdiet a, venenatis vitae, justo',
        'Nullam dictum felis eu pede mollis pretium',
        'Integer tincidunt',
        'Cras dapibus',
        'Vivamus elementum semper nisi',
        'Aenean vulputate eleifend tellus',
        'Aenean leo ligula, porttitor eu, consequat vitae, eleifend ac, enim',
        'Aliquam lorem ante, dapibus in, viverra quis, feugiat',
        'Aenean imperdiet. Etiam ultricies nisi vel',
        'Praesent adipiscing',
        'Integer ante arcu',
        'Curabitur ligula sapien',
        'Donec posuere vulputate'
    ];

    private static array $fixtureText = [
        'Lorem ipsum dolor sit amet, consectetuer adipiscing elit. Aenean commodo ligula eget dolor. Aenean massa.',
        'Cum sociis natoque penatibus et magnis dis parturient montes, nascetur ridiculus mus.',
        'Donec quam felis, ultricies nec, pellentesque eu, pretium quis, sem. Nulla consequat massa quis enim.',
        'Donec pede justo, fringilla vel, aliquet nec, vulputate eget, arcu. In enim justo, rhoncus ut, imperdiet.',
        'Nullam dictum felis eu pede mollis pretium. Integer tincidunt. Cras dapibus. Vivamus elementum semper nisi..',
        'Aenean leo ligula, porttitor eu, consequat vitae, eleifend ac, enim. Aliquam lorem ante, dapibus in, viverra,',
        'Phasellus viverra nulla ut metus varius laoreet. Quisque rutrum. Aenean imperdiet. Etiam ultricies nisi vel.',
        'Curabitur ullamcorper ultricies nisi. Nam eget dui. Etiam rhoncus.',
        'Maecenas tempus, tellus eget condimentum rhoncus, sem quam semper libero, sit amet adipiscing sem neque sed.',
        'Nam quam nunc, blandit vel, luctus pulvinar, hendrerit id, lorem. Maecenas nec odio et ante tincidunt tempus.',
        'Donec vitae sapien ut libero venenatis faucibus. Nullam quis ante. Etiam sit amet orci eget eros faucibus.',
        'Sed fringilla mauris sit amet nibh. Donec sodales sagittis magna. Sed consequat, leo eget bibendum sodales.',
        'Fusce vulputate eleifend sapien. Vestibulum purus quam, scelerisque ut, mollis sed, nonummy id, metus.',
        'Cras ultricies mi eu turpis hendrerit fringilla. Vestibulum ante ipsum primis in faucibus orci luctus.',
        'Nam pretium turpis et arcu. Duis arcu tortor, suscipit eget, imperdiet nec, imperdiet iaculis, ipsum.',
        'Integer ante arcu, accumsan a, consectetuer eget, posuere ut, mauris. Praesent adipiscing.',
        'Vestibulum volutpat pretium libero. Cras id dui. Aenean ut eros et nisl sagittis vestibulum.',
        'Sed lectus. Donec mollis hendrerit risus. Phasellus nec sem in justo pellentesque facilisis. Etiam imperdiet.',
        'Phasellus leo dolor, tempus non, auctor et, hendrerit quis, nisi. Curabitur ligula sapien, tincidunt non.',
        'Praesent congue erat at massa. Sed cursus turpis vitae tortor. Donec posuere vulputate arcu.',
    ];

    private static array $relatedEntities = [
        Contact::class => 'setRelatedContact',
        Account::class => 'setRelatedAccount',
    ];

    private array $entitiesCount = [];

    #[\Override]
    public function getDependencies(): array
    {
        return [
            LoadContactData::class,
            LoadAccountData::class,
            LoadUsersData::class
        ];
    }

    #[\Override]
    public function load(ObjectManager $manager): void
    {
        $caseManager = $this->container->get('oro_case.manager');
        for ($i = 0; $i < self::CASES_COUNT; ++$i) {
            $subject = self::$fixtureSubjects[$i];
            if ($manager->getRepository(CaseEntity::class)->findOneBySubject($subject)) {
                // Case with this title is already exist
                continue;
            }

            $case = $this->createCaseEntity($manager, $caseManager, $subject);
            $manager->persist($case);
        }
        $manager->flush();
    }

    private function createCaseEntity(
        ObjectManager $manager,
        CaseEntityManager $caseManager,
        string $subject
    ): ?CaseEntity {
        $owner = $this->getRandomEntity($manager, User::class);
        $assignedTo = $this->getRandomEntity($manager, User::class);
        $source = $this->getRandomEntity($manager, CaseSource::class);
        $status = $this->getRandomEntity($manager, CaseStatus::class);
        $priority = $this->getRandomEntity($manager, CasePriority::class);

        if (!$owner || !$assignedTo || !$source || !$status) {
            // If we don't have users, sources and status we cannot load fixture cases
            return null;
        }

        $case = $caseManager->createCase();
        $case->setSubject($subject);
        $case->setDescription($this->getRandomText());
        $case->setReportedAt($this->getRandomDate());

        $case->setOwner($owner);
        $case->setAssignedTo($assignedTo);
        $case->setSource($source);
        $case->setStatus($status);
        $case->setPriority($priority);
        $case->setOrganization($this->getReference('default_organization'));

        switch (rand(0, 1)) {
            case 0:
                $contact = $this->getRandomEntity($manager, Contact::class);
                $case->setRelatedContact($contact);
                break;
            case 1:
            default:
                $account = $this->getRandomEntity($manager, Account::class);
                $case->setRelatedAccount($account);
                break;
        }

        $commentsCount = rand(self::MIN_COMMENTS_PER_CASE, self::MAX_COMMENTS_PER_CASE);
        for ($i = 0; $i < $commentsCount; ++$i) {
            $comment = $this->createComment($manager, $caseManager, $this->getRandomText());
            $comment->setOrganization($this->getReference('default_organization'));
            $case->addComment($comment);
        }

        return $case;
    }

    private function createComment(ObjectManager $manager, CaseEntityManager $caseManager, string $text): CaseComment
    {
        $comment = $caseManager->createComment();
        $comment->setMessage($text);
        $comment->setOwner($this->getRandomEntity($manager, User::class));
        $comment->setPublic(rand(0, 5));
        $comment->setCreatedAt($this->getRandomDate());
        if (rand(0, 3) === 3) {
            $contact = $this->getRandomEntity($manager, Contact::class);
            $comment->setContact($contact);
        }
        if (rand(0, 5) === 5) {
            $updatedBy = $this->getRandomEntity($manager, User::class);
            $comment->setUpdatedBy($updatedBy);
            $comment->setUpdatedAt($this->getRandomDate());
        }
        return $comment;
    }

    private function getRandomEntity(ObjectManager $manager, string $entityClass): ?object
    {
        $count = $this->getEntityCount($manager, $entityClass);
        if ($count) {
            $qb = $manager->createQueryBuilder()
                ->select('e')
                ->from($entityClass, 'e')
                ->setFirstResult(rand(0, $count - 1))
                ->setMaxResults(1)
                ->orderBy('e.' . $manager->getClassMetadata($entityClass)->getSingleIdentifierFieldName());
            if (User::class === $entityClass) {
                $qb->where('e.organization = :organization')
                    ->setParameter('organization', $this->getReference('default_organization'));
            }

            return $qb->getQuery()->getSingleResult();
        }

        return null;
    }

    private function getEntityCount(ObjectManager $manager, string $entityClass): int
    {
        if (!isset($this->entitiesCount[$entityClass])) {
            $qb = $manager->createQueryBuilder()
                ->select('COUNT(e)')
                ->from($entityClass, 'e');
            if (User::class === $entityClass) {
                $qb->where('e.organization = :organization')
                    ->setParameter('organization', $this->getReference('default_organization'));
            }

            $this->entitiesCount[$entityClass] = (int)$qb->getQuery()->getSingleScalarResult();
        }

        return $this->entitiesCount[$entityClass];
    }

    private function getRandomDate(): \DateTime
    {
        $result = new \DateTime();
        $result->sub(new \DateInterval(sprintf('P%dDT%dM', rand(0, 30), rand(0, 1440))));

        return $result;
    }

    private function getRandomText(): string
    {
        return self::$fixtureText[random_int(0, \count(self::$fixtureText) - 1)];
    }
}
