<?php

namespace Oro\Bundle\ChannelBundle\Tests\Functional\Fixture;

use Doctrine\ORM\EntityManager;
use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\Common\DataFixtures\AbstractFixture;

use Symfony\Component\DependencyInjection\ContainerAwareTrait;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;

use Oro\Bundle\UserBundle\Entity\User;
use Oro\Bundle\AccountBundle\Entity\Account;
use Oro\Bundle\ChannelBundle\Builder\BuilderFactory;
use Oro\Bundle\ChannelBundle\Entity\LifetimeValueHistory;

class LoadLifetimeHistoryData extends AbstractFixture implements ContainerAwareInterface
{
    use ContainerAwareTrait;

    /** @var array */
    protected $channels = [];

    /** @var array */
    protected $accounts = [];

    /**
     * {@inheritdoc}
     *
     * @param EntityManager $manager
     */
    public function load(ObjectManager $manager)
    {
        $handle  = fopen(__DIR__ . DIRECTORY_SEPARATOR . 'data' . DIRECTORY_SEPARATOR . 'history_data.csv', 'r');
        $headers = fgetcsv($handle, 1000, ',');

        $user = $manager->getRepository(User::class)->createQueryBuilder('u')
            ->setMaxResults(1)
            ->getQuery()
            ->getSingleResult();

        if (!$user) {
            throw new \LogicException("User was not found");
        }

        while (($data = fgetcsv($handle, 1000, ',')) !== false) {
            $combined = array_combine($headers, $data);
            $logDate  = new \DateTime($combined['Created Date UTC'], new \DateTimeZone('UTC'));

            $historyEntry = new LifetimeValueHistory();
            $historyEntry->setAccount($this->ensureAccountCreated($manager, $user, $combined['Account name']));
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
            $builder = $this->getBuilderFactory()->createBuilder();
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
     * @param User $user
     * @param string $accountName
     *
     * @return Account
     */
    protected function ensureAccountCreated(EntityManager $em, User $user, $accountName)
    {
        if (!isset($this->accounts[$accountName])) {
            $account = new Account();
            $account->setName($accountName);
            $account->setOwner($user);

            $em->persist($account);
            $em->flush($account);
            $this->accounts[$accountName] = $account;
        }

        return $this->accounts[$accountName];
    }

    /**
     * @return BuilderFactory
     */
    private function getBuilderFactory()
    {
        return $this->container->get('oro_channel.builder.factory');
    }
}
