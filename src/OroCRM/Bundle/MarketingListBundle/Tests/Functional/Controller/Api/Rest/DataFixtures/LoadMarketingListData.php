<?php

namespace OroCRM\Bundle\MarketingListBundle\Tests\Functional\Controller\Api\Rest\DataFixtures;

use OroCRM\Bundle\MarketingListBundle\Entity\MarketingList;
use OroCRM\Bundle\MarketingListBundle\Entity\MarketingListType;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\Persistence\ObjectManager;

use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

class LoadMarketingListData extends AbstractFixture implements ContainerAwareInterface
{
    /**
     * @var ContainerInterface
     */
    private $container;

    /**
     * @param ContainerInterface $container
     */
    public function setContainer(ContainerInterface $container = null)
    {
        $this->container = $container;
    }

    /**
     * @param ObjectManager $manager
     */
    public function load(ObjectManager $manager)
    {
        $type = $manager
            ->getRepository('OroCRMMarketingListBundle:MarketingListType')
            ->find(MarketingListType::TYPE_DYNAMIC);

        $entity = new MarketingList();
        $entity
            ->setType($type)
            ->setName('list_name')
            ->setEntity('OroCRM\Bundle\ContactBundle\Entity\Contact')
            ->setOrganization($manager->getRepository('OroOrganizationBundle:Organization')->getFirst());

        $manager->persist($entity);
        $manager->flush($entity);
    }
}
