<?php

namespace OroCRM\Bundle\DemoDataBundle\Migrations\Data\Demo\ORM;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;

use OroCRM\Bundle\AccountBundle\Entity\Account;
use OroCRM\Bundle\CallBundle\Entity\Call;
use OroCRM\Bundle\ContactBundle\Entity\Contact;

class LoadCallData extends AbstractFixture implements DependentFixtureInterface
{
    protected $subjects = array(
        'Cold Call', 'Reminder of our scheduled meeting', 'Happy Birthday', 'The lease of office space'
    );

    protected $notes = array(
        'note1', 'note2'
    );

    /**
     * {@inheritdoc}
     */
    public function getDependencies()
    {
        return ['OroCRM\Bundle\DemoDataBundle\Migrations\Data\Demo\ORM\LoadContactData',];
    }

    /**
     * {@inheritDoc}
     */
    public function load(ObjectManager $om)
    {
        $this->persistDemoCalls($om);
        $om->flush();
    }

    /**
     * @param ObjectManager                                     $om
     */
    protected function persistDemoCalls(
        ObjectManager $om
    ) {
        $accounts = $om->getRepository('OroCRMAccountBundle:Account')->findAll();
        $contacts = $om->getRepository('OroCRMContactBundle:Contact')->findAll();
        $directions = array(
            'incoming' => $om->getRepository('OroCRMCallBundle:CallDirection')->findOneBy(array('name' => 'incoming')),
            'outgoing' => $om->getRepository('OroCRMCallBundle:CallDirection')->findOneBy(array('name' => 'outgoing'))
        );
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
            $call->setOwner($contact->getOwner());
            $call->setSubject($this->subjects[array_rand($this->subjects)]);
            $call->setDuration(
                new \DateTime(rand(0, 1) . ':' . rand(0, 59) . ':' . rand(0, 59), new \DateTimeZone('UTC'))
            );
            $randomPath = rand(1, 10);
            if ($randomPath > 2) {
                $call->setRelatedContact($contact);
                $call->setContactPhoneNumber($contact->getPrimaryPhone());
                $call->setDirection($directions['outgoing']);
            }

            if ($randomPath > 3) {
                if ($call->getRelatedContact()) {
                    $call->setRelatedAccount($call->getRelatedContact()->getAccounts()[0]);
                } else {
                    $call->setRelatedAccount($account);
                }
            }

            if (is_null($call->getContactPhoneNumber())) {
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
}
