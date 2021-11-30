<?php

namespace Oro\Bridge\CallCRM\Migrations\Data\Demo\ORM;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Oro\Bundle\AccountBundle\Entity\Account;
use Oro\Bundle\CallBundle\Entity\Call;
use Oro\Bundle\ContactBundle\Entity\Contact;
use Oro\Bundle\OrganizationBundle\Entity\Organization;
use Oro\Bundle\SecurityBundle\Authentication\Token\UsernamePasswordOrganizationToken;
use Oro\Bundle\UserBundle\Entity\User;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Loads new Call entities.
 */
class LoadCallData extends AbstractFixture implements DependentFixtureInterface, ContainerAwareInterface
{
    protected $subjects = [
        'Cold Call', 'Reminder of our scheduled meeting', 'Happy Birthday', 'The lease of office space'
    ];

    protected $notes = [
        'note1', 'note2'
    ];

    /**
     * @var Organization
     */
    protected $organization;

    /** @var ContainerInterface */
    protected $container;

    /**
     * {@inheritdoc}
     */
    public function getDependencies()
    {
        return ['Oro\Bundle\DemoDataBundle\Migrations\Data\Demo\ORM\LoadContactData',];
    }

    /**
     * {@inheritDoc}
     */
    public function setContainer(ContainerInterface $container = null)
    {
        $this->container = $container;
    }

    /**
     * {@inheritDoc}
     */
    public function load(ObjectManager $om)
    {
        $this->organization = $this->getReference('default_organization');
        $this->persistDemoCalls($om);
        $om->flush();

        $tokenStorage = $this->container->get('security.token_storage');
        $tokenStorage->setToken(null);
    }

    /**
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    protected function persistDemoCalls(
        ObjectManager $om
    ) {
        $accounts = $om->getRepository('OroAccountBundle:Account')->findBy(['organization' => $this->organization]);
        $contacts = $om->getRepository('OroContactBundle:Contact')->findBy(['organization' => $this->organization]);
        $callStatus = $om->getRepository('OroCallBundle:CallStatus')->findOneBy([
            'name' => 'completed',
        ]);
        $directions = [
            'incoming' => $om->getRepository('OroCallBundle:CallDirection')->findOneBy(['name' => 'incoming']),
            'outgoing' => $om->getRepository('OroCallBundle:CallDirection')->findOneBy(['name' => 'outgoing'])
        ];
        $contactCount = count($contacts);
        $accountCount = count($accounts);
        for ($i = 0; $i < 100; ++$i) {
            $contactRandom = rand(0, $contactCount - 1);
            $accountRandom = rand(0, $accountCount - 1);
            /** @var Contact $contact */
            $contact = $contacts[$contactRandom];
            /** @var Account $account */
            $account = $accounts[$accountRandom];
            $call = new Call();
            $call->setCallStatus($callStatus);
            $call->setOrganization($this->organization);
            $call->setOwner($contact->getOwner());
            $call->setSubject($this->subjects[array_rand($this->subjects)]);
            $call->setDuration(rand(0, 4800));

            if ($call->supportActivityTarget(get_class($contact->getOwner()))) {
                $call->addActivityTarget($contact->getOwner());
            }

            $randomPath = rand(1, 10);

            if ($randomPath > 2) {
                if ($call->supportActivityTarget(get_class($contact))) {
                    $this->setSecurityContext($contact->getOwner());
                    $call->addActivityTarget($contact);
                }
                $contactPrimaryPhone = $contact->getPrimaryPhone();
                if ($contactPrimaryPhone) {
                    $call->setPhoneNumber($contactPrimaryPhone->getPhone());
                }
                $call->setDirection($directions['outgoing']);
            }

            if ($randomPath > 3) {
                /** @var Contact[] $relatedContacts */
                $relatedContacts = $call->getActivityTargets('Oro\Bundle\ContactBundle\Entity\Contact');
                if ($relatedContacts) {
                    if ($call->supportActivityTarget(get_class($relatedContacts[0]->getAccounts()[0]))) {
                        $call->addActivityTarget($relatedContacts[0]->getAccounts()[0]);
                    }
                } else {
                    if ($call->supportActivityTarget(get_class($account))) {
                        $call->addActivityTarget($account);
                    }
                }
            }

            $phone = $call->getPhoneNumber();
            if (empty($phone)) {
                $phone = rand(1000000000, 9999999999);
                $phone = sprintf(
                    "%s-%s-%s",
                    substr($phone, 0, 3),
                    substr($phone, 3, 3),
                    substr($phone, 6)
                );
                $call->setPhoneNumber($phone);
                $call->setDirection($directions['incoming']);
            }
            $om->persist($call);
        }
    }

    /**
     * @param string $startDate
     * @param string $endDate
     *
     * @return bool|string
     */
    protected function randomDate($startDate, $endDate)
    {
        // Convert to timestamps
        $min = strtotime($startDate);
        $max = strtotime($endDate);

        // Generate random number using above bounds
        $val = rand($min, $max);

        // Convert back to desired date format
        return date('Y-m-d H:i:s', $val);
    }

    /**
     * @param User $user
     */
    protected function setSecurityContext($user)
    {
        $tokenStorage = $this->container->get('security.token_storage');
        $token = new UsernamePasswordOrganizationToken(
            $user,
            $user->getUsername(),
            'main',
            $this->organization,
            $user->getUserRoles()
        );
        $tokenStorage->setToken($token);
    }
}
