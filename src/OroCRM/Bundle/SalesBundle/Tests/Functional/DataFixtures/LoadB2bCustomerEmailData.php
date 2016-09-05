<?php

namespace OroCRM\Bundle\SalesBundle\Tests\Functional\DataFixtures;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;

use OroCRM\Bundle\SalesBundle\Tests\Functional\DataFixtures\LoadB2bCustomerEntitiesData;
use OroCRM\Bundle\SalesBundle\Entity\B2bCustomerEmail;

class LoadB2bCustomerEmailData extends AbstractFixture implements DependentFixtureInterface
{
    const FIRST_ENTITY_NAME  = 'test1@test.test';
    const SECOND_ENTITY_NAME = 'test2@test.test';
    const THIRD_ENTITY_NAME  = 'test3@test.test';

    public function getDependencies()
    {
        return [
            'OroCRM\Bundle\SalesBundle\Tests\Functional\DataFixtures\LoadB2bCustomerEntitiesData'
        ];
    }

    /**
     * @var array
     */
    protected $b2bCustomerEmailData = [
        [
            'email' => self::FIRST_ENTITY_NAME,
            'primary'  => true,
        ],
        [
            'email' => self::SECOND_ENTITY_NAME,
            'primary'  => false,
        ],
        [
            'email' => self::THIRD_ENTITY_NAME,
            'primary'  => false,
        ]
    ];

    /**
     * {@inheritdoc}
     */
    public function load(ObjectManager $manager)
    {
        $customer = $this->getReference('B2bCustomer_' . LoadB2bCustomerEntitiesData::FIRST_ENTITY_NAME);

        foreach ($this->b2bCustomerEmailData as $b2bCustomerEmailData) {
            $customerEmail = new B2bCustomerEmail();
            $customerEmail->setPrimary($b2bCustomerEmailData['primary']);
            $customerEmail->setOwner($customer);
            $customerEmail->setEmail($b2bCustomerEmailData['email']);
            $this->setReference('B2bCustomerEmail_Several_' . $b2bCustomerEmailData['email'], $customerEmail);
            $manager->persist($customerEmail);
        }

        $customer2 = $this->getReference('B2bCustomer_' . LoadB2bCustomerEntitiesData::SECOND_ENTITY_NAME);
        $customerEmail = new B2bCustomerEmail();
        $customerEmail->setPrimary($this->b2bCustomerEmailData[0]['primary']);
        $customerEmail->setOwner($customer2);
        $customerEmail->setEmail($this->b2bCustomerEmailData[0]['email']);
        $this->setReference('B2bCustomerEmail_Single_' . $this->b2bCustomerEmailData[0]['email'], $customerEmail);
        $manager->persist($customerEmail);


        $manager->flush();
    }
}
