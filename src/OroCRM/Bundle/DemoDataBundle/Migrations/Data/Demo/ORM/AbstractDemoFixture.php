<?php

namespace OroCRM\Bundle\DemoDataBundle\Migrations\Data\Demo\ORM;

use Doctrine\ORM\EntityManager;
use Doctrine\Common\DataFixtures\AbstractFixture;

use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;

use Oro\Bundle\UserBundle\Entity\User;

abstract class AbstractDemoFixture extends AbstractFixture implements ContainerAwareInterface
{
    /** @var  EntityManager */
    protected $em;

    /** @var array */
    private $userIds;

    /** @var int */
    private $usersCount = 0;

    /**
     * {@inheritDoc}
     */
    public function setContainer(ContainerInterface $container = null)
    {
        $this->em = $container->get('doctrine')->getManager();
    }

    /**
     * @return User
     */
    protected function getRandomUser()
    {
        if (null === $this->userIds) {
            $this->userIds = $this->getUserIds();
            shuffle($this->userIds);
            $this->usersCount = count($this->userIds) - 1;
        }

        $random = rand(0, $this->usersCount);

        return $this->em->getReference('OroUserBundle:User', $this->userIds[$random]);
    }

    /**
     * @return array
     */
    private function getUserIds()
    {
        $items = $this->em->getRepository('OroUserBundle:User')->createQueryBuilder('u')
            ->select('u.id')
            ->getQuery()
            ->getArrayResult();

        return array_map(
            function ($item) {
                return $item['id'];
            },
            $items
        );
    }
}
