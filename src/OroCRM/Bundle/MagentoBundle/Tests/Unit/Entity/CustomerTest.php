<?php

namespace OroCRM\Bundle\MagentoBundle\Tests\Unit\Entity;

class CustomerTest extends AbstractEntityTestCase
{
    const TEST_ORIGIN_ID = 123;
    const TEST_IS_ACTIVE = false;
    const TEST_STRING    = 'string';

    /**
     * {@inheritDoc}
     */
    public function getEntityFQCN()
    {
        return 'OroCRM\Bundle\MagentoBundle\Entity\Customer';
    }

    /**
     * {@inheritDoc}
     */
    public function getSetDataProvider()
    {
        $date    = new \DateTime('now');
        $group   = $this->getMock('OroCRM\Bundle\MagentoBundle\Entity\CustomerGroup');
        $website = $this->getMock('OroCRM\Bundle\MagentoBundle\Entity\Website');
        $store   = $this->getMock('OroCRM\Bundle\MagentoBundle\Entity\Store');
        $contact = $this->getMock('OroCRM\Bundle\ContactBundle\Entity\Contact');
        $account = $this->getMock('OroCRM\Bundle\AccountBundle\Entity\Account');

        return [
            'createdAt'  => ['createdAt', $date, $date],
            'updatedAt'  => ['updatedAt', $date, $date],
            'group'      => ['group', $group, $group],
            'website'    => ['website', $website, $website],
            'store'      => ['store', $store, $store],
            'contact'    => ['contact', $contact, $contact],
            'account'    => ['account', $account, $account],
            'originId'   => ['originId', self::TEST_ORIGIN_ID, self::TEST_ORIGIN_ID],
            'vat'        => ['vat', self::TEST_STRING . 'vat', self::TEST_STRING . 'vat'],
            'isActive'   => ['isActive', self::TEST_IS_ACTIVE, self::TEST_IS_ACTIVE],
        ];
    }

    public function testGettersSetters()
    {
        $this->entity->setFirstName(self::TEST_STRING . 'first');
        $this->entity->setLastName(self::TEST_STRING . 'last');

        $this->entity->addAddress($this->getMock('OroCRM\Bundle\MagentoBundle\Entity\Address'));

        $this->assertInstanceOf('Doctrine\Common\Collections\Collection', $this->entity->getAddresses());
        $this->assertFalse($this->entity->getAddressByOriginId(1));
    }
}
