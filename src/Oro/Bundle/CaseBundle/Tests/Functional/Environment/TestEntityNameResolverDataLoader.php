<?php

namespace Oro\Bundle\CaseBundle\Tests\Functional\Environment;

use Doctrine\Common\DataFixtures\ReferenceRepository;
use Doctrine\ORM\EntityManagerInterface;
use Oro\Bundle\CaseBundle\Entity\CaseEntity;
use Oro\Bundle\CaseBundle\Entity\CaseMailboxProcessSettings;
use Oro\Bundle\CaseBundle\Entity\CasePriority;
use Oro\Bundle\CaseBundle\Entity\CaseStatus;
use Oro\Bundle\EmailBundle\Entity\Mailbox;
use Oro\Bundle\EntityBundle\Provider\EntityNameProviderInterface;
use Oro\Bundle\EntityBundle\Tests\Functional\Environment\TestEntityNameResolverDataLoaderInterface;

class TestEntityNameResolverDataLoader implements TestEntityNameResolverDataLoaderInterface
{
    private TestEntityNameResolverDataLoaderInterface $innerDataLoader;

    public function __construct(TestEntityNameResolverDataLoaderInterface $innerDataLoader)
    {
        $this->innerDataLoader = $innerDataLoader;
    }

    #[\Override]
    public function loadEntity(
        EntityManagerInterface $em,
        ReferenceRepository $repository,
        string $entityClass
    ): array {
        if (CaseEntity::class === $entityClass) {
            $case = new CaseEntity();
            $case->setOrganization($repository->getReference('organization'));
            $case->setOwner($repository->getReference('user'));
            $case->setPriority($em->find(CasePriority::class, CasePriority::PRIORITY_NORMAL));
            $case->setStatus($em->find(CaseStatus::class, CaseStatus::STATUS_IN_PROGRESS));
            $case->setReportedAt(new \DateTime('2023-05-10 12:00:00', new \DateTimeZone('UTC')));
            $case->setSubject('Test Case');
            $repository->setReference('case', $case);
            $em->persist($case);
            $em->flush();

            return ['case'];
        }

        if (CaseMailboxProcessSettings::class === $entityClass) {
            $mailbox = new Mailbox();
            $mailbox->setLabel('Test Mailbox');
            $mailbox->setEmail('test@example.org');
            $em->persist($mailbox);
            $settings = new CaseMailboxProcessSettings();
            $settings->setOwner($repository->getReference('user'));
            $settings->setPriority($em->find(CasePriority::class, CasePriority::PRIORITY_NORMAL));
            $settings->setStatus($em->find(CaseStatus::class, CaseStatus::STATUS_IN_PROGRESS));
            $settings->setAssignTo($repository->getReference('user'));
            $settings->setMailbox($mailbox);
            $mailbox->setProcessSettings($settings);
            $repository->setReference('caseMailboxProcessSettings', $settings);
            $em->persist($settings);
            $em->flush();

            return ['caseMailboxProcessSettings'];
        }

        return $this->innerDataLoader->loadEntity($em, $repository, $entityClass);
    }

    #[\Override]
    public function getExpectedEntityName(
        ReferenceRepository $repository,
        string $entityClass,
        string $entityReference,
        ?string $format,
        ?string $locale
    ): string {
        if (CaseEntity::class === $entityClass) {
            return 'Test Case';
        }
        if (CaseMailboxProcessSettings::class === $entityClass) {
            return EntityNameProviderInterface::SHORT === $format
                ? (string)$repository->getReference($entityReference)->getId()
                : sprintf('Item #%d', $repository->getReference($entityReference)->getId());
        }

        return $this->innerDataLoader->getExpectedEntityName(
            $repository,
            $entityClass,
            $entityReference,
            $format,
            $locale
        );
    }
}
