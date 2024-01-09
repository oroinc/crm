<?php

namespace Oro\Bridge\CallCRM\Migrations\Data\Demo\ORM;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Oro\Bundle\AccountBundle\Entity\Account;
use Oro\Bundle\CallBundle\Entity\Call;
use Oro\Bundle\CallBundle\Entity\CallDirection;
use Oro\Bundle\CallBundle\Entity\CallStatus;
use Oro\Bundle\ContactBundle\Entity\Contact;
use Oro\Bundle\DemoDataBundle\Migrations\Data\Demo\ORM\LoadContactData;
use Oro\Bundle\SecurityBundle\Authentication\Token\UsernamePasswordOrganizationToken;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerAwareTrait;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

/**
 * Loads new Call entities.
 */
class LoadCallData extends AbstractFixture implements DependentFixtureInterface, ContainerAwareInterface
{
    use ContainerAwareTrait;

    private array $subjects = [
        'Cold Call', 'Reminder of our scheduled meeting', 'Happy Birthday', 'The lease of office space'
    ];

    private array $notes = [
        'note1', 'note2'
    ];

    /**
     * {@inheritDoc}
     */
    public function getDependencies(): array
    {
        return [LoadContactData::class];
    }

    /**
     * {@inheritDoc}
     */
    public function load(ObjectManager $manager): void
    {
        $tokenStorage = $this->container->get('security.token_storage');
        $this->persistDemoCalls($manager, $tokenStorage);
        $manager->flush();
        $tokenStorage->setToken(null);
    }

    /**
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    private function persistDemoCalls(ObjectManager $manager, TokenStorageInterface $tokenStorage): void
    {
        $organization = $this->getReference('default_organization');
        $accounts = $manager->getRepository(Account::class)->findBy(['organization' => $organization]);
        $contacts = $manager->getRepository(Contact::class)->findBy(['organization' => $organization]);
        $callStatus = $manager->getRepository(CallStatus::class)->findOneBy(['name' => 'completed']);
        $directions = [
            'incoming' => $manager->getRepository(CallDirection::class)->findOneBy(['name' => 'incoming']),
            'outgoing' => $manager->getRepository(CallDirection::class)->findOneBy(['name' => 'outgoing'])
        ];
        $contactCount = \count($contacts);
        $accountCount = \count($accounts);
        for ($i = 0; $i < 100; ++$i) {
            $contactRandom = rand(0, $contactCount - 1);
            $accountRandom = rand(0, $accountCount - 1);
            /** @var Contact $contact */
            $contact = $contacts[$contactRandom];
            /** @var Account $account */
            $account = $accounts[$accountRandom];
            $call = new Call();
            $call->setCallStatus($callStatus);
            $call->setOrganization($organization);
            $call->setOwner($contact->getOwner());
            $call->setSubject($this->subjects[array_rand($this->subjects)]);
            $call->setDuration(rand(0, 4800));

            if ($call->supportActivityTarget(\get_class($contact->getOwner()))) {
                $call->addActivityTarget($contact->getOwner());
            }

            $randomPath = rand(1, 10);

            if ($randomPath > 2) {
                $this->addActivityTarget($call, $contact, $tokenStorage);
                $contactPrimaryPhone = $contact->getPrimaryPhone();
                if ($contactPrimaryPhone) {
                    $call->setPhoneNumber($contactPrimaryPhone->getPhone());
                }
                $call->setDirection($directions['outgoing']);
            }

            if ($randomPath > 3) {
                /** @var Contact[] $relatedContacts */
                $relatedContacts = $call->getActivityTargets(Contact::class);
                if ($relatedContacts) {
                    if ($call->supportActivityTarget(\get_class($relatedContacts[0]->getAccounts()[0]))) {
                        $call->addActivityTarget($relatedContacts[0]->getAccounts()[0]);
                    }
                } else {
                    if ($call->supportActivityTarget(\get_class($account))) {
                        $call->addActivityTarget($account);
                    }
                }
            }

            $phone = $call->getPhoneNumber();
            if (empty($phone)) {
                $phone = rand(1000000000, 9999999999);
                $phone = sprintf(
                    '%s-%s-%s',
                    substr($phone, 0, 3),
                    substr($phone, 3, 3),
                    substr($phone, 6)
                );
                $call->setPhoneNumber($phone);
                $call->setDirection($directions['incoming']);
            }
            $manager->persist($call);
        }
    }

    private function addActivityTarget(Call $call, object $target, TokenStorageInterface $tokenStorage): void
    {
        if ($call->supportActivityTarget(\get_class($target))) {
            $user = $target->getOwner();
            $tokenStorage->setToken(new UsernamePasswordOrganizationToken(
                $user,
                'main',
                $this->getReference('default_organization'),
                $user->getUserRoles()
            ));
            $call->addActivityTarget($target);
        }
    }
}
