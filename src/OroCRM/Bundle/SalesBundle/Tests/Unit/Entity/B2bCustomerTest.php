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

    public function testPhones()
    {
        $phoneOne = new B2bCustomerPhone('06001122334455');
        $phoneTwo = new B2bCustomerPhone('07001122334455');
        $phoneThree = new B2bCustomerPhone('08001122334455');
        $phones = array($phoneOne, $phoneTwo);
        $customer = new B2bCustomer();
        $this->assertSame($customer, $customer->resetPhones($phones));
        $actual = $customer->getPhones();
        $this->assertInstanceOf('Doctrine\Common\Collections\ArrayCollection', $actual);
        $this->assertEquals($phones, $actual->toArray());
        $this->assertSame($customer, $customer->addPhone($phoneTwo));
        $actual = $customer->getPhones();
        $this->assertInstanceOf('Doctrine\Common\Collections\ArrayCollection', $actual);
        $this->assertEquals($phones, $actual->toArray());
        $this->assertSame($customer, $customer->addPhone($phoneThree));
        $actual = $customer->getPhones();
        $this->assertInstanceOf('Doctrine\Common\Collections\ArrayCollection', $actual);
        $this->assertEquals(array($phoneOne, $phoneTwo, $phoneThree), $actual->toArray());
        $this->assertSame($customer, $customer->removePhone($phoneOne));
        $actual = $customer->getPhones();
        $this->assertInstanceOf('Doctrine\Common\Collections\ArrayCollection', $actual);
        $this->assertEquals(array(1 => $phoneTwo, 2 => $phoneThree), $actual->toArray());
        $this->assertSame($customer, $customer->removePhone($phoneOne));
        $actual = $customer->getPhones();
        $this->assertInstanceOf('Doctrine\Common\Collections\ArrayCollection', $actual);
        $this->assertEquals(array(1 => $phoneTwo, 2 => $phoneThree), $actual->toArray());
    }
    public function testGetPrimaryPhone()
    {
        $customer = new B2bCustomer();
        $this->assertNull($customer->getPrimaryPhone());
        $phone = new B2bCustomerPhone('06001122334455');
        $customer->addPhone($phone);
        $this->assertNull($customer->getPrimaryPhone());
        $customer->setPrimaryPhone($phone);
        $this->assertSame($phone, $customer->getPrimaryPhone());
        $phone2 = new B2bCustomerPhone('22001122334455');
        $customer->addPhone($phone2);
        $customer->setPrimaryPhone($phone2);
        $this->assertSame($phone2, $customer->getPrimaryPhone());
        $this->assertFalse($phone->isPrimary());
    }

    public function testEmails()
    {
        $emailOne = new B2bCustomerEmail('email-one@example.com');
        $emailTwo = new B2bCustomerEmail('email-two@example.com');
        $emailThree = new B2bCustomerEmail('email-three@example.com');
        $emails = array($emailOne, $emailTwo);
        $customer = new B2bCustomer();
        $this->assertSame($customer, $customer->resetEmails($emails));
        $actual = $customer->getEmails();
        $this->assertInstanceOf('Doctrine\Common\Collections\ArrayCollection', $actual);
        $this->assertEquals($emails, $actual->toArray());
        $this->assertSame($customer, $customer->addEmail($emailTwo));
        $actual = $customer->getEmails();
        $this->assertInstanceOf('Doctrine\Common\Collections\ArrayCollection', $actual);
        $this->assertEquals($emails, $actual->toArray());
        $this->assertSame($customer, $customer->addEmail($emailThree));
        $actual = $customer->getEmails();
        $this->assertInstanceOf('Doctrine\Common\Collections\ArrayCollection', $actual);
        $this->assertEquals(array($emailOne, $emailTwo, $emailThree), $actual->toArray());
        $this->assertSame($customer, $customer->removeEmail($emailOne));
        $actual = $customer->getEmails();
        $this->assertInstanceOf('Doctrine\Common\Collections\ArrayCollection', $actual);
        $this->assertEquals(array(1 => $emailTwo, 2 => $emailThree), $actual->toArray());
        $this->assertSame($customer, $customer->removeEmail($emailOne));
        $actual = $customer->getEmails();
        $this->assertInstanceOf('Doctrine\Common\Collections\ArrayCollection', $actual);
        $this->assertEquals(array(1 => $emailTwo, 2 => $emailThree), $actual->toArray());
    }
    public function testGetPrimaryEmail()
    {
        $customer = new B2bCustomer();
        $this->assertNull($customer->getPrimaryEmail());
        $email = new B2bCustomerEmail('email-one@example.com');
        $customer->addEmail($email);
        $this->assertNull($customer->getPrimaryEmail());
        $customer->setPrimaryEmail($email);
        $this->assertSame($email, $customer->getPrimaryEmail());
        $email2 = new B2bCustomerEmail('email-two@example.com');
        $customer->addEmail($email2);
        $customer->setPrimaryEmail($email2);
        $this->assertSame($email2, $customer->getPrimaryEmail());
        $this->assertFalse($email->isPrimary());
    }
}
