<?php

namespace OroCRM\Bundle\ChannelBundle\Tests\Functional\Fixture;

use Doctrine\ORM\EntityManager;
use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\Common\DataFixtures\AbstractFixture;

use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;

use Oro\Bundle\UserBundle\Entity\User;

use OroCRM\Bundle\AccountBundle\Entity\Account;
use OroCRM\Bundle\ChannelBundle\Builder\BuilderFactory;
use OroCRM\Bundle\ChannelBundle\Entity\LifetimeValueHistory;

class LoadLifetimeHistoryData extends AbstractFixture implements ContainerAwareInterface
{
    /** @var ObjectManager */
    protected $em;

    /** @var BuilderFactory */
    protected $factory;

    /** @var array */
    protected $channels = [];

    /** @var array */
    protected $accounts = [];

    /** @var User */
    protected $user;

    /**
     * {@inheritDoc}
     */
    public function setContainer(ContainerInterface $container = null)
    {
        $this->factory = $container->get('orocrm_channel.builder.factory');
    }

    /**
     * {@inheritdoc}
     */
    public function load(ObjectManager $manager)
    {
        $handle  = fopen(__DIR__ . DIRECTORY_SEPARATOR . 'data' . DIRECTORY_SEPARATOR . 'history_data.csv', 'r');
        $headers = fgetcsv($handle, 1000, ',');

        while (($data = fgetcsv($handle, 1000, ',')) !== false) {
            $combined = array_combine($headers, $data);
            $logDate  = new \DateTime($combined['Created Date UTC'], new \DateTimeZone('UTC'));

            $historyEntry = new LifetimeValueHistory();
            $historyEntry->setAccount($this->ensureAccountCreated($manager, $combined['Account name']));
            $historyEntry->setDataChannel($this->ensureChannelCreated($manager, $combined['Channel name'], $logDate));
            $historyEntry->setCreatedAt($logDate);
            $historyEntry->setStatus($combined['Status']);
            $historyEntry->setAmount($combined['Amount']);

            $manager->persist($historyEntry);
        }

        $manager->flush();
        fclose($handle);
    }

    /**
     * @param EntityManager $em
     * @param string        $name
     * @param \DateTime     $created
     *
     * @return mixed
     */
    protected function ensureChannelCreated(EntityManager $em, $name, \DateTime $created)
    {
        if (!isset($this->channels[$name])) {
            $builder = $this->factory->createBuilder();
            $builder->setChannelType('custom');
            $builder->setName($name);
            $builder->setCreatedAt($created);

            $channel = $builder->getChannel();
            $em->persist($channel);
            $em->flush($channel);
            $this->channels[$name] = $channel;
        }

        return $this->channels[$name];
    }

    /**
     * @param EntityManager $em
     * @param string        $name
     *
     * @return Account
     */
    protected function ensureAccountCreated(EntityManager $em, $name)
    {
        if (!isset($this->accounts[$name])) {
            $account = new Account();
            $account->setName($name);
            $account->setOwner($this->getUser($em));

            $em->persist($account);
            $em->flush($account);
            $this->accounts[$name] = $account;
        }

        return $this->accounts[$name];
    }

    /**
     * @param EntityManager $em
     *
     * @return User
     */
    protected function getUser(EntityManager $em)
    {
        if (!$this->user) {
            $this->user = $em->getRepository('OroUserBundle:User')->createQueryBuilder('u')
                ->setMaxResults(1)
                ->getQuery()
                ->getSingleResult();
        }

        return $this->user;
    }
}
