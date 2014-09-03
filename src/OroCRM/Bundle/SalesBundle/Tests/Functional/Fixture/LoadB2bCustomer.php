<?php

namespace OroCRM\Bundle\SalesBundle\Tests\Functional\Fixture;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\Persistence\ObjectManager;

use OroCRM\Bundle\AccountBundle\Entity\Account;
use OroCRM\Bundle\ChannelBundle\Builder\BuilderFactory;
use OroCRM\Bundle\ChannelBundle\Entity\Channel;
use OroCRM\Bundle\SalesBundle\Entity\B2bCustomer;

use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

class LoadB2bCustomer extends AbstractFixture implements ContainerAwareInterface
{
    const CUSTOMER_NAME = 'b2bCustomer name';
    const CHANNEL_TYPE  = 'b2b';
    const CHANNEL_NAME  = 'b2b Channel';
    const ACCOUNT_NAME  = 'some account name';

    /** @var ObjectManager */
    protected $em;

    /** @var BuilderFactory */
    protected $factory;

    /**
     * {@inheritDoc}
     */
    public function setContainer(ContainerInterface $container = null)
    {
        $this->factory = $container->get('orocrm_channel.builder.factory');
    }

    /**
     * @param ObjectManager $manager
     *
     * @return Channel
     */
    public function load(ObjectManager $manager)
    {
        $this->em = $manager;

        $customer = new B2bCustomer();
        $customer->setAccount($this->createAccount());
        $customer->setName(self::CUSTOMER_NAME);
        $customer->setDataChannel($this->createChannel());

        $this->em->persist($customer);
        $this->em->flush();

        $this->setReference('default_b2bcustomer', $customer);
        $this->setReference('default_b2bcustomer_account', $customer->getAccount());
        $this->setReference('default_b2bcustomer_channel', $customer->getDataChannel());

        return $customer;
    }

    /**
     * @return Account
     */
    protected function createAccount()
    {
        $account = new Account();
        $account->setName(self::ACCOUNT_NAME);

        $this->em->persist($account);

        return $account;
    }

    /**
     * @return Channel
     */
    protected function createChannel()
    {
        $builder = $this->factory->createBuilder();
        $builder->setName(self::CHANNEL_NAME);
        $builder->setChannelType(self::CHANNEL_TYPE);
        $builder->setStatus(Channel::STATUS_ACTIVE);
        $builder->setEntities(
            [
                'OroCRM\Bundle\SalesBundle\Entity\B2bCustomer',
                'OroCRM\Bundle\SalesBundle\Entity\Lead',
                'OroCRM\Bundle\SalesBundle\Entity\Opportunity',
                'OroCRM\Bundle\SalesBundle\Entity\SalesFunnel',
            ]
        );
        $channel = $builder->getChannel();
        $this->em->persist($channel);
        
        return $channel;
    }
}
