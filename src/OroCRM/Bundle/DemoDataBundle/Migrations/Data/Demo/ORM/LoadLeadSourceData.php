<?php

namespace OroCRM\Bundle\DemoDataBundle\Migrations\Data\Demo\ORM;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\Persistence\ObjectManager;

use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

use Oro\Bundle\EntityExtendBundle\Entity\Repository\EnumValueRepository;
use Oro\Bundle\EntityExtendBundle\Tools\ExtendHelper;

class LoadLeadSourceData extends AbstractFixture implements ContainerAwareInterface
{
    /** @var array */
    protected $data = [
        'Website'         => false,
        'Direct Mail'     => false,
        'Affiliate'       => false,
        'Email Marketing' => false,
        'Outbound'        => false,
        'Partner'         => false
    ];

    /** @var ContainerInterface */
    protected $container;

    /**
     * {@inheritdoc}
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
        $className = ExtendHelper::buildEnumValueClassName('lead_source');

        /** @var EnumValueRepository $enumRepo */
        $enumRepo = $manager->getRepository($className);

        $priority = 1;
        foreach ($this->data as $optionSetLabel => $isDefault) {
            $enumOption = $enumRepo->createEnumValue(
                $optionSetLabel,
                $priority++,
                $isDefault
            );

            $manager->persist($enumOption);
        }

        $manager->flush();
    }
}
