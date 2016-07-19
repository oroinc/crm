<?php

namespace OroCRM\Bundle\SalesBundle\Tests\Functional\Fixture;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;

use OroCRM\Bundle\SalesBundle\Entity\LeadPhone;

class LoadLeadPhoneData extends AbstractFixture implements DependentFixtureInterface
{
    const FIRST_ENTITY_NAME  = '1111111';
    const SECOND_ENTITY_NAME = '2222222';
    const THIRD_ENTITY_NAME  = '3333333';

    public function getDependencies()
    {
        return [
            'OroCRM\Bundle\SalesBundle\Tests\Functional\Fixture\LoadSalesBundleFixtures'
        ];
    }

    /**
     * @var array
     */
    protected $leadPhoneData = [
        [
            'phone' => self::FIRST_ENTITY_NAME,
            'primary'  => true,
        ],
        [
            'phone' => self::SECOND_ENTITY_NAME,
            'primary'  => false,
        ],
        [
            'phone' => self::THIRD_ENTITY_NAME,
            'primary'  => false,
        ]
    ];

    /**
     * {@inheritdoc}
     */
    public function load(ObjectManager $manager)
    {
        $lead = $this->getReference('default_lead');
        foreach ($this->leadPhoneData as $leadPhoneData) {
            $leadPhone = new LeadPhone();
            $leadPhone->setPrimary($leadPhoneData['primary']);
            $leadPhone->setOwner($lead);
            $leadPhone->setPhone($leadPhoneData['phone']);

            $this->setReference('LeadPhone_Several_' . $leadPhoneData['phone'], $leadPhone);
            $manager->persist($leadPhone);
        }

        $lead2 = $this->getReference('second_lead');
        $leadPhone = new LeadPhone();
        $leadPhone->setPrimary($this->leadPhoneData[0]['primary']);
        $leadPhone->setOwner($lead2);
        $leadPhone->setPhone($this->leadPhoneData[0]['phone']);
        $this->setReference('LeadPhone_Single_' . $this->leadPhoneData[0]['phone'], $leadPhone);
        $manager->persist($leadPhone);

        $manager->flush();
    }
}
