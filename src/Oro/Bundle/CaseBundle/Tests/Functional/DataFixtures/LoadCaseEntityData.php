<?php

namespace Oro\Bundle\CaseBundle\Tests\Functional\DataFixtures;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Oro\Bundle\OrganizationBundle\Entity\Organization;
use Oro\Bundle\TestFrameworkBundle\Tests\Functional\DataFixtures\LoadOrganization;
use Oro\Bundle\TestFrameworkBundle\Tests\Functional\DataFixtures\LoadUser;
use Oro\Bundle\UserBundle\Entity\User;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerAwareTrait;

class LoadCaseEntityData extends AbstractFixture implements ContainerAwareInterface, DependentFixtureInterface
{
    use ContainerAwareTrait;

    private array $casesData = [
        [
            'subject'       => 'Case #1',
            'description'   => 'Case #1: Description',
            'comments'      => [
                [
                    'message'      => 'Case #1 Comment #1',
                    'public'    => true,
                    'createdAt' => '2014-01-01 13:00:00',
                    'contact'   => 'oro_case_contact'
                ],
                [
                    'message'      => 'Case #1 Comment #2',
                    'public'        => true,
                    'createdAt' => '2014-01-01 14:00:00',
                ],
                [
                    'message'   => 'Case #1 Comment #3',
                    'public'    => false,
                    'createdAt' => '2014-01-01 15:00:00',
                ]
            ],
            'reportedAt'     => '2014-01-01 13:00:00',
            'relatedContact' => 'oro_case_contact'
        ],
        [
            'subject'       => 'Case #2',
            'description'   => 'Case #2: Description',
            'comments'      => [],
            'reportedAt'    => '2014-01-01 14:00:00'
        ],
        [
            'subject'       => 'Case #3',
            'description'   => 'Case #3: Description',
            'comments'      => [],
            'reportedAt'    => '2014-01-01 15:00:00'
        ],
    ];

    /**
     * {@inheritDoc}
     */
    public function getDependencies(): array
    {
        return [LoadContactData::class, LoadOrganization::class, LoadUser::class];
    }

    /**
     * {@inheritDoc}
     */
    public function load(ObjectManager $manager): void
    {
        $caseManager = $this->container->get('oro_case.manager');
        /** @var User $adminUser */
        $adminUser = $this->getReference(LoadUser::USER);
        /** @var Organization $organization */
        $organization = $this->getReference(LoadOrganization::ORGANIZATION);
        foreach ($this->casesData as $caseData) {
            $case = $caseManager->createCase();
            $case->setSubject($caseData['subject']);
            $case->setDescription($caseData['description']);
            $case->setReportedAt(new \DateTime($caseData['reportedAt'], new \DateTimeZone('UTC')));
            $case->setOrganization($organization);
            $case->setOwner($adminUser);
            if (isset($caseData['relatedContact'])) {
                $case->setRelatedContact($this->getReference($caseData['relatedContact']));
            }

            foreach ($caseData['comments'] as $commentData) {
                $comment = $caseManager->createComment($case);
                $comment->setMessage($commentData['message']);
                $comment->setPublic($commentData['public']);
                $comment->setCreatedAt(new \DateTime($commentData['createdAt'], new \DateTimeZone('UTC')));
                $comment->setOrganization($organization);
                $comment->setOwner($adminUser);
                if (isset($commentData['contact'])) {
                    $comment->setContact($this->getReference($commentData['contact']));
                }
            }

            $manager->persist($case);
        }
        $manager->flush();
    }
}
