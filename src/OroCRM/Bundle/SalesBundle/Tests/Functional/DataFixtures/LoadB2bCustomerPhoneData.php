<?php

namespace OroCRM\Bundle\SalesBundle\Tests\Functional\DataFixtures;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;

use OroCRM\Bundle\SalesBundle\Tests\Functional\DataFixtures\LoadB2bCustomerEntitiesData;
use OroCRM\Bundle\SalesBundle\Entity\B2bCustomerPhone;

class LoadB2bCustomerPhoneData extends AbstractFixture implements DependentFixtureInterface
{
    const FIRST_ENTITY_NAME  = '1111111';
    const SECOND_ENTITY_NAME = '2222222';
    const THIRD_ENTITY_NAME  = '3333333';

    public function getDependencies()
    {
        return [
            'OroCRM\Bundle\SalesBundle\Tests\Functional\DataFixtures\LoadB2bCustomerEntitiesData'
        ];
    }

    /**
     * @var array
     */
    protected $b2bCustomerPhoneData = [
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
        $customer = $this->getReference('B2bCustomer_' . LoadB2bCustomerEntitiesData::FIRST_ENTITY_NAME);
        foreach ($this->b2bCustomerPhoneData as $b2bCustomerPhoneData) {
            $customerPhone = new B2bCustomerPhone();
            $customerPhone->setPrimary($b2bCustomerPhoneData['primary']);
            $customerPhone->setOwner($customer);
            $customerPhone->setPhone($b2bCustomerPhoneData['phone']);

            $this->setReference('B2bCustomerPhone_Several_' . $b2bCustomerPhoneData['phone'], $customerPhone);
            $manager->persist($customerPhone);
        }

        $customer2 = $this->getReference('B2bCustomer_' . LoadB2bCustomerEntitiesData::SECOND_ENTITY_NAME);
        $customerPhone = new B2bCustomerPhone();
        $customerPhone->setPrimary($this->b2bCustomerPhoneData[0]['primary']);
        $customerPhone->setOwner($customer2);
        $customerPhone->setPhone($this->b2bCustomerPhoneData[0]['phone']);
        $this->setReference('B2bCustomerPhone_Single_' . $this->b2bCustomerPhoneData[0]['phone'], $customerPhone);
        $manager->persist($customerPhone);

        $manager->flush();
    }
}
