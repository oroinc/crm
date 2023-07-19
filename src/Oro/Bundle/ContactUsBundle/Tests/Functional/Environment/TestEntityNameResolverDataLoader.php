<?php

namespace Oro\Bundle\ContactUsBundle\Tests\Functional\Environment;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\DataFixtures\ReferenceRepository;
use Doctrine\ORM\EntityManagerInterface;
use Oro\Bundle\ContactUsBundle\Entity\ContactReason;
use Oro\Bundle\ContactUsBundle\Entity\ContactRequest;
use Oro\Bundle\EntityBundle\Provider\EntityNameProviderInterface;
use Oro\Bundle\EntityBundle\Tests\Functional\Environment\TestEntityNameResolverDataLoaderInterface;
use Oro\Bundle\LocaleBundle\Entity\Localization;
use Oro\Bundle\LocaleBundle\Entity\LocalizedFallbackValue;

class TestEntityNameResolverDataLoader implements TestEntityNameResolverDataLoaderInterface
{
    private TestEntityNameResolverDataLoaderInterface $innerDataLoader;

    public function __construct(TestEntityNameResolverDataLoaderInterface $innerDataLoader)
    {
        $this->innerDataLoader = $innerDataLoader;
    }

    public function loadEntity(
        EntityManagerInterface $em,
        ReferenceRepository $repository,
        string $entityClass
    ): array {
        if (ContactRequest::class === $entityClass) {
            $request = new ContactRequest();
            $request->setOwner($repository->getReference('organization'));
            $request->setCustomerName('Test Company');
            $request->setFirstName('John');
            $request->setLastName('Smith');
            $request->setPhone('123-123');
            $request->setEmailAddress('john_request@example.com');
            $request->setComment('Test Contact Request');
            $repository->setReference('contactRequest', $request);
            $em->persist($request);
            $em->flush();

            return ['contactRequest'];
        }

        if (ContactReason::class === $entityClass) {
            $reason = new ContactReason();
            $reason->setTitles(new ArrayCollection([
                $this->createLocalizedFallbackValue($em, 'Test Reason'),
                $this->createLocalizedFallbackValue(
                    $em,
                    'Test Reason (de_DE)',
                    $repository->getReference('de_DE')
                ),
                $this->createLocalizedFallbackValue(
                    $em,
                    'Test Reason (fr_FR)',
                    $repository->getReference('fr_FR')
                )
            ]));
            $repository->setReference('contactReason', $reason);
            $em->persist($reason);
            $em->flush();

            return ['contactReason'];
        }

        return $this->innerDataLoader->loadEntity($em, $repository, $entityClass);
    }

    public function getExpectedEntityName(
        ReferenceRepository $repository,
        string $entityClass,
        string $entityReference,
        ?string $format,
        ?string $locale
    ): string {
        if (ContactRequest::class === $entityClass) {
            return EntityNameProviderInterface::SHORT === $format
                ? 'John'
                : 'John Smith';
        }
        if (ContactReason::class === $entityClass) {
            return 'Localization de_DE' === $locale
                ? 'Test Reason (de_DE)'
                : 'Test Reason';
        }

        return $this->innerDataLoader->getExpectedEntityName(
            $repository,
            $entityClass,
            $entityReference,
            $format,
            $locale
        );
    }

    private function createLocalizedFallbackValue(
        EntityManagerInterface $em,
        string $value,
        ?Localization $localization = null
    ): LocalizedFallbackValue {
        $lfv = new LocalizedFallbackValue();
        $lfv->setString($value);
        if (null !== $localization) {
            $lfv->setLocalization($localization);
        }
        $em->persist($lfv);

        return $lfv;
    }
}
