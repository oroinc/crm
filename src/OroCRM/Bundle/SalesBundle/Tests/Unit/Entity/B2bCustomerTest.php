<?php

namespace OroCRM\Bundle\SalesBundle\Tests\Unit\Entity;

use OroCRM\Bundle\SalesBundle\Entity\B2bCustomer;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Util\ClassUtils;
use OroCRM\Bundle\SalesBundle\Entity\B2bCustomerEmail;
use OroCRM\Bundle\SalesBundle\Entity\B2bCustomerPhone;

class B2bCustomerTest extends \PHPUnit_Framework_TestCase
{
    const TEST_ID = 12;
    const TEST_NAME = 'test name';

    /** @var B2bCustomer */
    protected $entity;

    protected function setUp()
    {
        $this->entity = new B2bCustomer();
    }

    protected function tearDown()
    {
        unset($this->entity);
    }

    /**
     * @dataProvider getSetDataProvider
     */
    public function testGetSet($property, $value, $expected = null)
    {
        if (null !== $value) {
            call_user_func_array([$this->entity, 'set' . ucfirst($property)], [$value]);
        }
        $this->assertSame($expected, call_user_func([$this->entity, 'get' . ucfirst($property)]));
    }

    /**
     * @return array
     */
    public function getSetDataProvider()
    {
        $name         = uniqid('name');
        $address      = $this->getMock('Oro\Bundle\AddressBundle\Entity\Address');
        $account      = $this->getMock('OroCRM\Bundle\AccountBundle\Entity\Account');
        $contact      = $this->getMock('OroCRM\Bundle\ContactBundle\Entity\Contact');
        $channel      = $this->getMock('OroCRM\Bundle\ChannelBundle\Entity\Channel');
        $owner        = $this->getMock('Oro\Bundle\UserBundle\Entity\User');
        $organization = $this->getMock('Oro\Bundle\OrganizationBundle\Entity\Organization');
        $date         = new \DateTime();
        $lifetime     = 12.22;

        return [
            'id'              => ['id', null, null],
            'name'            => ['name', $name, $name],
            '$lifetime'       => ['lifetime', $lifetime, $lifetime],
            'shippingAddress' => ['shippingAddress', $address, $address],
            'billingAddress'  => ['billingAddress', $address, $address],
            'account'         => ['account', $account, $account],
            'contact'         => ['contact', $contact, $contact],
            'dataChannel'     => ['dataChannel', $channel, $channel],
            'owner'           => ['owner', $owner, $owner],
            'organization'    => ['organization', $organization, $organization],
            'createdAt'       => ['createdAt', $date, $date],
            'updatedAt'       => ['updatedAt', $date, $date],
        ];
    }

    /**
     * @return array
     */
    public function phonesDataProvider()
    {
        return [
            [
                [
                    'first'  => new B2bCustomerPhone('06001122334455'),
                    'second' => new B2bCustomerPhone('07001122334455'),
                    'third'  => new B2bCustomerPhone('08001122334455')
                ]
            ]
        ];
    }

    /**
     * @return array
     */
    public function emailsDataProvider()
    {
        return [
            [
                [
                    'first'  => new B2bCustomerEmail('email-one@example.com'),
                    'second' => new B2bCustomerEmail('email-two@example.com'),
                    'third'  => new B2bCustomerEmail('email-three@example.com')
                ]
            ]
        ];
    }

    public function testPrePersist()
    {
        $this->assertNull($this->entity->getCreatedAt());

        $this->entity->prePersist();

        $this->assertInstanceOf('DateTime', $this->entity->getCreatedAt());
        $this->assertLessThan(3, $this->entity->getCreatedAt()->diff(new \DateTime())->s);
    }

    public function testPreUpdate()
    {
        $this->assertNull($this->entity->getUpdatedAt());

        $this->entity->preUpdate();

        $this->assertInstanceOf('DateTime', $this->entity->getUpdatedAt());
        $this->assertLessThan(3, $this->entity->getUpdatedAt()->diff(new \DateTime())->s);
    }

    public function testLeadsInteraction()
    {
        $result = $this->entity->getLeads();
        $this->assertInstanceOf('Doctrine\Common\Collections\ArrayCollection', $result);
        $this->assertCount(0, $result);

        $lead = $this->getMock('OroCRM\Bundle\SalesBundle\Entity\Lead');
        $this->entity->addLead($lead);
        $this->assertCount(1, $this->entity->getLeads());
        $this->assertTrue($this->entity->getLeads()->contains($lead));

        $this->entity->removeLead($lead);
        $result = $this->entity->getLeads();
        $this->assertCount(0, $result);

        $newCollection = new ArrayCollection();
        $this->entity->setLeads($newCollection);
        $this->assertNotSame($result, $this->entity->getLeads());
        $this->assertSame($newCollection, $this->entity->getLeads());
    }

    public function testOpportunitiesInteraction()
    {
        $result = $this->entity->getOpportunities();
        $this->assertInstanceOf('Doctrine\Common\Collections\ArrayCollection', $result);
        $this->assertCount(0, $result);

        $opportunity = $this->getMock('OroCRM\Bundle\SalesBundle\Entity\Opportunity');
        $this->entity->addOpportunity($opportunity);
        $this->assertCount(1, $this->entity->getOpportunities());
        $this->assertTrue($this->entity->getOpportunities()->contains($opportunity));

        $this->entity->removeOpportunity($opportunity);
        $result = $this->entity->getLeads();
        $this->assertCount(0, $result);

        $newCollection = new ArrayCollection();
        $this->entity->setOpportunities($newCollection);
        $this->assertNotSame($result, $this->entity->getOpportunities());
        $this->assertSame($newCollection, $this->entity->getOpportunities());
    }

    public function testToSting()
    {
        $this->entity->setName(self::TEST_NAME);
        $this->assertSame(self::TEST_NAME, (string)$this->entity);
    }

    /**
     * @dataProvider phonesDataProvider
     * @param $phones
     */
    public function testPhones($phones)
    {
        $customerPhones = [$phones['first'], $phones['second']];
        $customer = new B2bCustomer();
        $this->assertSame($customer, $customer->resetPhones($customerPhones));

        $actual = $customer->getPhones();
        $this->assertInstanceOf('Doctrine\Common\Collections\ArrayCollection', $actual);
        $this->assertEquals($customerPhones, $actual->toArray());
        $this->assertSame($customer, $customer->addPhone($phones['second']));

        $actual = $customer->getPhones();
        $this->assertInstanceOf('Doctrine\Common\Collections\ArrayCollection', $actual);
        $this->assertEquals($customerPhones, $actual->toArray());
        $this->assertSame($customer, $customer->addPhone($phones['third']));

        $actual = $customer->getPhones();
        $this->assertInstanceOf('Doctrine\Common\Collections\ArrayCollection', $actual);
        $this->assertEquals([$phones['first'], $phones['second'], $phones['third']], $actual->toArray());
        $this->assertSame($customer, $customer->removePhone($phones['first']));

        $actual = $customer->getPhones();
        $this->assertInstanceOf('Doctrine\Common\Collections\ArrayCollection', $actual);
        $this->assertEquals([1 => $phones['second'], 2 => $phones['third']], $actual->toArray());
        $this->assertSame($customer, $customer->removePhone($phones['first']));

        $actual = $customer->getPhones();
        $this->assertInstanceOf('Doctrine\Common\Collections\ArrayCollection', $actual);
        $this->assertEquals([1 => $phones['second'], 2 => $phones['third']], $actual->toArray());
    }

    /**
     * @dataProvider phonesDataProvider
     * @param $phones
     */
    public function testGetPrimaryPhone($phones)
    {
        $customer = new B2bCustomer();
        $this->assertNull($customer->getPrimaryPhone());
        $customer->addPhone($phones['first']);
        $this->assertNull($customer->getPrimaryPhone());
        $customer->setPrimaryPhone($phones['first']);
        $this->assertSame($phones['first'], $customer->getPrimaryPhone());
        $customer->addPhone($phones['second']);
        $customer->setPrimaryPhone($phones['second']);
        $this->assertSame($phones['second'], $customer->getPrimaryPhone());
        $this->assertFalse($phones['first']->isPrimary());
    }

    /**
     * @dataProvider emailsDataProvider
     * @param $emails
     */
    public function testEmails($emails)
    {
        $customerEmails = [$emails['first'], $emails['second']];
        $customer = new B2bCustomer();
        $this->assertSame($customer, $customer->resetEmails($customerEmails));

        $actual = $customer->getEmails();
        $this->assertInstanceOf('Doctrine\Common\Collections\ArrayCollection', $actual);
        $this->assertEquals($customerEmails, $actual->toArray());
        $this->assertSame($customer, $customer->addEmail($emails['second']));

        $actual = $customer->getEmails();
        $this->assertInstanceOf('Doctrine\Common\Collections\ArrayCollection', $actual);
        $this->assertEquals($customerEmails, $actual->toArray());
        $this->assertSame($customer, $customer->addEmail($emails['third']));

        $actual = $customer->getEmails();
        $this->assertInstanceOf('Doctrine\Common\Collections\ArrayCollection', $actual);
        $this->assertEquals([$emails['first'], $emails['second'], $emails['third']], $actual->toArray());
        $this->assertSame($customer, $customer->removeEmail($emails['first']));

        $actual = $customer->getEmails();
        $this->assertInstanceOf('Doctrine\Common\Collections\ArrayCollection', $actual);
        $this->assertEquals([1 => $emails['second'], 2 => $emails['third']], $actual->toArray());
        $this->assertSame($customer, $customer->removeEmail($emails['first']));

        $actual = $customer->getEmails();
        $this->assertInstanceOf('Doctrine\Common\Collections\ArrayCollection', $actual);
        $this->assertEquals([1 => $emails['second'], 2 => $emails['third']], $actual->toArray());
    }

    /**
     * @dataProvider emailsDataProvider
     * @param $emails
     */
    public function testGetPrimaryEmail($emails)
    {
        $customer = new B2bCustomer();
        $this->assertNull($customer->getPrimaryEmail());
        $email = $emails['first'];
        $customer->addEmail($email);
        $this->assertNull($customer->getPrimaryEmail());
        $customer->setPrimaryEmail($email);
        $this->assertSame($email, $customer->getPrimaryEmail());
        $email2 = $emails['second'];
        $customer->addEmail($email2);
        $customer->setPrimaryEmail($email2);
        $this->assertSame($email2, $customer->getPrimaryEmail());
        $this->assertFalse($email->isPrimary());
    }
}
