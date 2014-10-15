<?php

namespace OroCRM\Bundle\CallBundle\Tests\Functional\DataFixtures;

use OroCRM\Bundle\CallBundle\Entity\CallDirection;
use OroCRM\Bundle\CallBundle\Entity\CallStatus;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\Persistence\ObjectManager;

class LoadCallDataFixtures extends AbstractFixture implements ContainerAwareInterface
{
    const STATUS_NAME       = 'Call Status Name #1';
    const DIRECTION_NAME    = 'Call Direction Name #1';

    /**
     * @var ContainerInterface
     */
    private $container;

    /**
     * {@inheritdoc}
     */
    public function load(ObjectManager $manager)
    {

        $callStatus = new CallStatus(self::STATUS_NAME);
        $callStatus->setLabel(self::STATUS_NAME);
        $manager->persist($callStatus);
        $callDirection = new CallDirection(self::DIRECTION_NAME);
        $callDirection->setLabel(self::DIRECTION_NAME);
        $manager->persist($callDirection);
        $manager->flush();
    }

    /**
     * {@inheritdoc}
     */
    public function setContainer(ContainerInterface $container = null)
    {
        $this->container = $container;
    }
}
