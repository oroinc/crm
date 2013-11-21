<?php

namespace OroCRM\Bundle\MagentoBundle\Tests\Unit\Entity;

class CustomerTest extends AbstractEntityTestCase
{
    const TEST_ORIGINAL_ID = 123;

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
            'originalId' => ['originalId', self::TEST_ORIGINAL_ID, self::TEST_ORIGINAL_ID],
        ];
    }
}
